<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Прибирає суфікс «-R» з назви бренду в контенті: PELOVIT-R → PELOVIT,
 * Пеловіт-Р → Пеловіт. Залишок ребрендингу: верстка вже чиста, а тексти
 * товарів, статей і FAQ лишилися зі старою назвою.
 *
 * Що команда НЕ чіпає свідомо:
 *  — slug товарів і статей: це живі URL, їх зміна вимагає 301-редіректів;
 *  — будь-що всередині посилань (href/src, http-адреси): там та сама назва
 *    є частиною шляху, і підміна зламала б адресу (напр. instagram.com/pelovit_r).
 *
 * Ідемпотентна: повторний запуск нічого не змінює.
 */
class StripBrandSuffix extends Command
{
    protected $signature = 'brand:strip-r
                            {--dry-run : тільки показати, що змінилося б}
                            {--force : не питати підтвердження (для прода/CI)}';

    protected $description = 'Прибрати «-R» з назви бренду в текстах товарів, статей і FAQ';

    /**
     * Назва бренду + розділювач + самотня літера R/Р, за якою не йде буква чи
     * цифра. Lookahead відсікає слова на кшталт «Pelovit Розроблено».
     */
    private const BRAND = '/(PELOVIT|Pelovit|pelovit|ПЕЛОВІТ|Пеловіт|ПЕЛОВИТ|Пеловит)'
        .'(?:\s*[-–—_]\s*|[\s\x{00A0}]+)'
        .'[RrРр](?![\p{L}\p{N}])/u';

    /** Фрагменти, де підміна зламала б адресу. */
    private const URLS = '~(?:href|src|action|srcset|data-href)\s*=\s*(?:"[^"]*"|\'[^\']*\')'
        .'|https?://[^\s"\'<>]+~iu';

    /** @var array<string, list<string>> таблиця => текстові колонки (без slug) */
    private const TARGETS = [
        'products' => [
            'name', 'description', 'instruction',
            'meta_title', 'meta_description', 'meta_keywords',
            'og_title', 'og_description', 'brand',
        ],
        'posts' => [
            'title', 'excerpt', 'body',
            'meta_title', 'meta_description', 'meta_keywords',
            'og_title', 'og_description',
        ],
        'faqs' => ['question', 'answer'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $plan = $this->collectChanges();

        if ($plan === []) {
            $this->info('Нічого прибирати: «-R» у текстах не знайдено.');

            return self::SUCCESS;
        }

        $this->renderPlan($plan);

        if ($dryRun) {
            $this->comment('Пробний запуск — нічого не записано.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Застосувати зміни?', false)) {
            $this->comment('Скасовано.');

            return self::SUCCESS;
        }

        $this->apply($plan);

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<int, array<string, string>>> таблиця => [id => [колонка => нове значення]]
     */
    private function collectChanges(): array
    {
        $plan = [];

        foreach (self::TARGETS as $table => $columns) {
            DB::table($table)->select(array_merge(['id'], $columns))->orderBy('id')
                ->chunk(200, function ($rows) use ($table, $columns, &$plan): void {
                    foreach ($rows as $row) {
                        foreach ($columns as $column) {
                            $value = $row->{$column};

                            if (! is_string($value) || $value === '') {
                                continue;
                            }

                            $clean = $this->strip($value);

                            if ($clean !== $value) {
                                $plan[$table][$row->id][$column] = $clean;
                            }
                        }
                    }
                });
        }

        return $plan;
    }

    /** Прибирає суфікс, не торкаючись посилань. */
    private function strip(string $text): string
    {
        $masks = [];

        $masked = preg_replace_callback(self::URLS, function (array $m) use (&$masks): string {
            $key = "\x00".count($masks)."\x00";
            $masks[$key] = $m[0];

            return $key;
        }, $text);

        $replaced = preg_replace(self::BRAND, '$1', (string) $masked);

        return strtr((string) $replaced, $masks);
    }

    /** @param array<string, array<int, array<string, string>>> $plan */
    private function renderPlan(array $plan): void
    {
        $rowsTotal = 0;
        $fieldsTotal = 0;
        $byColumn = [];

        foreach ($plan as $table => $records) {
            $rowsTotal += count($records);

            foreach ($records as $columns) {
                foreach ($columns as $column => $_) {
                    $byColumn["{$table}.{$column}"] = ($byColumn["{$table}.{$column}"] ?? 0) + 1;
                    $fieldsTotal++;
                }
            }
        }

        ksort($byColumn);

        $this->table(
            ['поле', 'записів'],
            array_map(fn ($k, $v) => [$k, $v], array_keys($byColumn), $byColumn)
        );

        $this->line("Разом: {$fieldsTotal} полів у {$rowsTotal} записах.");
        $this->newLine();
        $this->comment('Приклади:');

        $shown = 0;

        foreach ($plan as $table => $records) {
            foreach ($records as $id => $columns) {
                foreach ($columns as $column => $after) {
                    if ($shown >= 5) {
                        break 3;
                    }

                    $this->line("  {$table}#{$id}.{$column}: ".$this->snippet($after));
                    $shown++;
                }
            }
        }

        $this->newLine();
    }

    private function snippet(string $text): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', strip_tags($text)));

        return mb_strlen($flat) > 110 ? mb_substr($flat, 0, 110).'…' : $flat;
    }

    /** @param array<string, array<int, array<string, string>>> $plan */
    private function apply(array $plan): void
    {
        $updated = 0;

        DB::transaction(function () use ($plan, &$updated): void {
            foreach ($plan as $table => $records) {
                foreach ($records as $id => $columns) {
                    DB::table($table)->where('id', $id)->update($columns);
                    $updated++;
                }
            }
        });

        $this->info("Готово: оновлено {$updated} записів.");
        $this->comment('Slug не змінювався — URL лишилися ті самі.');
    }
}
