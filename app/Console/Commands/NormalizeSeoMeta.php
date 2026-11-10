<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Лагодить meta_title і meta_description товарів та статей — спадок переїзду
 * з WordPress. Там у мету механічно залили назву й кусок опису, тому на проді
 * тайтл товару дорівнював назві («Остео-гель 120мл» — ні бренду, ні запиту),
 * а в description їхали HTML-сутності (&#8217;), склеєні пункти списку й
 * обрізок інструкції довше 160 символів.
 *
 * Що робить:
 *  — title: {назва} — купити в Україні | PELOVIT, з огляду на ліміт 60 символів
 *    (для довгих назв суфікс коротшає, у назвах з «PELOVIT» бренд не дублюється);
 *  — description: збирає з HTML опису, де блокові теги стають межами речень
 *    (інакше strip_tags склеює <li> в кашу), декодує сутності й ріже до 160
 *    по межі речення.
 *
 * Чого НЕ робить свідомо:
 *  — не чіпає мету, написану руками: title перезаписується лише якщо він
 *    порожній або дорівнює назві, description — лише якщо порожній або є
 *    механічним відрізком опису. Тому команда ідемпотентна й безпечна
 *    після того, як контент-менеджер напише свої тексти;
 *  — не вигадує маркетингових текстів: зібране з опису краще за поточне
 *    сміття, але осмислені тексти під запити — це робота копірайтера;
 *  — не торкається slug, og_* і meta_keywords (og_* самі падають у meta_*,
 *    keywords пошуковики ігнорують).
 */
class NormalizeSeoMeta extends Command
{
    protected $signature = 'seo:normalize-meta
                            {--dry-run : тільки показати, що змінилося б}
                            {--force : не питати підтвердження (для прода/CI)}
                            {--only= : products|posts, за замовчуванням обидва}';

    protected $description = 'Нормалізувати meta_title і meta_description товарів та статей';

    /** Google ріже тайтл приблизно на 60 символах, сніпет — на 160. */
    private const TITLE_MAX = 60;

    private const DESC_MAX = 160;

    /** Коротше за це обрізати по межі речення не варто — краще по слову. */
    private const DESC_MIN = 90;

    public function handle(): int
    {
        $only = $this->option('only');

        if ($only !== null && ! in_array($only, ['products', 'posts'], true)) {
            $this->error('--only приймає лише products або posts.');

            return self::FAILURE;
        }

        $plan = [];

        if ($only !== 'posts') {
            $plan['products'] = $this->planProducts();
        }

        if ($only !== 'products') {
            $plan['posts'] = $this->planPosts();
        }

        $plan = array_filter($plan);

        if ($plan === []) {
            $this->info('Мета вже в порядку — змінювати нічого.');

            return self::SUCCESS;
        }

        $this->renderPlan($plan);

        if ($this->option('dry-run')) {
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

    /** @return array<int, array{name: string, changes: array<string, string>}> */
    private function planProducts(): array
    {
        $plan = [];

        DB::table('products')
            ->select('id', 'name', 'description', 'meta_title', 'meta_description')
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$plan): void {
                foreach ($rows as $row) {
                    $changes = [];
                    $source = $this->plainText($row->description);

                    $title = $this->shopTitle((string) $row->name);

                    if ($this->titleIsAuto((string) $row->meta_title, (string) $row->name) && $title !== (string) $row->meta_title) {
                        $changes['meta_title'] = $title;
                    }

                    $description = $this->trimTo($source);

                    if (
                        $description !== ''
                        && $this->descriptionIsAuto((string) $row->meta_description, $source)
                        && $description !== (string) $row->meta_description
                    ) {
                        $changes['meta_description'] = $description;
                    }

                    if ($changes !== []) {
                        $plan[$row->id] = ['name' => (string) $row->name, 'changes' => $changes];
                    }
                }
            });

        return $plan;
    }

    /** @return array<int, array{name: string, changes: array<string, string>}> */
    private function planPosts(): array
    {
        $plan = [];

        DB::table('posts')
            ->select('id', 'title', 'excerpt', 'body', 'meta_title', 'meta_description')
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$plan): void {
                foreach ($rows as $row) {
                    $changes = [];

                    // Анонс написаний людиною, тому він кращий за початок тексту.
                    $source = $this->plainText($row->excerpt) ?: $this->plainText($row->body);

                    $title = $this->articleTitle((string) $row->title);

                    if ($this->titleIsAuto((string) $row->meta_title, (string) $row->title) && $title !== (string) $row->meta_title) {
                        $changes['meta_title'] = $title;
                    }

                    $description = $this->trimTo($source);

                    if (
                        $description !== ''
                        && $this->descriptionIsAuto((string) $row->meta_description, $source)
                        && $description !== (string) $row->meta_description
                    ) {
                        $changes['meta_description'] = $description;
                    }

                    if ($changes !== []) {
                        $plan[$row->id] = ['name' => (string) $row->title, 'changes' => $changes];
                    }
                }
            });

        return $plan;
    }

    /** Порожній тайтл або просто продубльована назва — писали не руками. */
    private function titleIsAuto(string $meta, string $name): bool
    {
        return $this->fingerprint($meta) === '' || $this->fingerprint($meta) === $this->fingerprint($name);
    }

    /**
     * Чи можна перезаписати опис. Так — якщо він порожній, є механічним
     * відрізком тексту сторінки (саме так залив WordPress) або має об'єктивні
     * ознаки зіпсованого: HTML-сутності, довжина понад ліміт сніпета,
     * суцільний потік без жодної крапки.
     *
     * Частина товарів отримала мету з блоку переваг, якого в базі вже немає,
     * тому підстрокою вона не ловиться — лишаються саме ці ознаки.
     *
     * Акуратний текст, написаний руками, під жодну з умов не підпадає.
     */
    private function descriptionIsAuto(string $meta, string $source): bool
    {
        $meta = trim($meta);

        if ($this->fingerprint($meta) === '') {
            return true;
        }

        // Сутності в тексті означають, що його різали з HTML, а не писали.
        if (preg_match('/&(?:#\d+|#x[0-9a-f]+|[a-z]+);/i', $meta)) {
            return true;
        }

        // Google все одно обріже — такий опис писали не під сніпет.
        if (mb_strlen($meta) > self::DESC_MAX) {
            return true;
        }

        // Злиплий список переваг: довгий текст без жодного знака кінця речення.
        if (mb_strlen($meta) > 80 && ! preg_match('/[.!?]/u', $meta)) {
            return true;
        }

        $source = $this->fingerprint($source);

        return $source !== '' && str_contains($source, mb_substr($this->fingerprint($meta), 0, 60));
    }

    /** Нормалізована форма для порівнянь: без розмітки, пунктуації й регістру. */
    private function fingerprint(string $text): string
    {
        $flat = $this->plainText($text);
        $flat = preg_replace('/[^\p{L}\p{N}]+/u', '', $flat) ?? '';

        return mb_strtolower($flat);
    }

    /**
     * HTML у плоский текст так, щоб пункти списку й заголовки не склеювалися:
     * strip_tags сам по собі з «<li>а</li><li>б</li>» робить «аб».
     */
    private function plainText(?string $html): string
    {
        if (! is_string($html) || trim($html) === '') {
            return '';
        }

        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~<(?:br|/p|/li|/h[1-6]|/div|/tr|/td|/blockquote)[^>]*>~i', "\n", $text) ?? '';
        $text = strip_tags($text);

        $lines = [];

        foreach (preg_split('/\n+/u', $text) ?: [] as $line) {
            $line = trim(preg_replace('/[\x{00A0}\s]+/u', ' ', $line) ?? '');
            $line = preg_replace('/\s+([,.;:!?])/u', '$1', $line) ?? '';

            // Маркер списку чи номер кроку в сніпеті — сміття: «• Швидко зігріває»
            // і «1. Прийняти ванну» читаються як обрізок розмітки, а не як текст.
            $line = preg_replace('/^(?:[•·▪◦‣*\-–—]+|\d{1,2}[.)])\s*/u', '', $line) ?? '';

            // Анонси статей — це один абзац «1. … 2. … 3. …», тому номери
            // кроків сидять і всередині рядка, після кінця речення.
            $line = preg_replace('/(?<=[.!?])\s*\d{1,2}[.)](?=\s)/u', '', $line) ?? '';

            if ($line === '') {
                continue;
            }

            // Пункт списку без крапки в кінці зліпився б з наступним реченням.
            if (! preg_match('/[.!?;:,]$/u', $line)) {
                $line .= '.';
            }

            $lines[] = $line;
        }

        return implode(' ', $lines);
    }

    private function trimTo(string $text, int $max = self::DESC_MAX): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $max) {
            return $this->finishSentence($text);
        }

        $cut = mb_substr($text, 0, $max);

        // Найкращий обрив — межа речення: сніпет не виглядає обрубаним.
        if (preg_match('/^(.*[.!?])\s/su', $cut, $m) && mb_strlen($m[1]) >= self::DESC_MIN) {
            return $this->finishSentence($m[1]);
        }

        $space = mb_strrpos($cut, ' ');

        if ($space !== false) {
            $cut = mb_substr($cut, 0, $space);
        }

        // rtrim ріже по байтах: у списку є «–» і «—», тому він здатен відкусити
        // половину багатобайтного символу й зламати UTF-8 у кінці рядка.
        return preg_replace('/[\s,;:.\-–—]+$/u', '', $cut).'…';
    }

    /**
     * Прибирає хвіст, який лишається від розмітки: кома чи «;» від пункту
     * списку та самотній номер кроку, текст якого не вліз у ліміт.
     */
    private function finishSentence(string $text): string
    {
        $text = preg_replace('/\s*\d{1,2}[.)]\s*$/u', '', $text) ?? $text;

        return preg_replace('/\s*[,;:]$/u', '.', trim($text)) ?? $text;
    }

    /** Тайтл товару: назва + найдовший суфікс, що влазить у 60 символів. */
    private function shopTitle(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        $suffixes = preg_match('/pelovit|пеловіт/iu', $name)
            ? [' — купити в Україні', ' — купити', '']
            : [' — купити в Україні | PELOVIT', ' — купити | PELOVIT', ' | PELOVIT', ''];

        return $this->withSuffix($name, $suffixes);
    }

    /** Тайтл статті: «купити» тут не до місця, лишається тільки бренд. */
    private function articleTitle(string $title): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? '');

        $suffixes = preg_match('/pelovit|пеловіт/iu', $title) ? [''] : [' | PELOVIT', ''];

        return $this->withSuffix($title, $suffixes);
    }

    /** @param list<string> $suffixes */
    private function withSuffix(string $base, array $suffixes): string
    {
        foreach ($suffixes as $suffix) {
            if (mb_strlen($base.$suffix) <= self::TITLE_MAX) {
                return $base.$suffix;
            }
        }

        return $base;
    }

    /** @param array<string, array<int, array{name: string, changes: array<string, string>}>> $plan */
    private function renderPlan(array $plan): void
    {
        $rows = [];

        foreach ($plan as $table => $records) {
            $byField = [];

            foreach ($records as $record) {
                foreach (array_keys($record['changes']) as $field) {
                    $byField[$field] = ($byField[$field] ?? 0) + 1;
                }
            }

            foreach ($byField as $field => $count) {
                $rows[] = ["{$table}.{$field}", $count];
            }

            $rows[] = ["{$table}: записів", count($records)];
        }

        $this->table(['поле', 'записів'], $rows);
        $this->newLine();
        $this->comment('Приклади:');

        foreach ($plan as $table => $records) {
            $shown = 0;

            foreach ($records as $id => $record) {
                if ($shown >= 3) {
                    break;
                }

                $this->line("  <info>{$table}#{$id}</info> {$record['name']}");

                foreach ($record['changes'] as $field => $value) {
                    $this->line("    {$field} (".mb_strlen($value)."): {$value}");
                }

                $shown++;
            }
        }

        $this->newLine();
    }

    /** @param array<string, array<int, array{name: string, changes: array<string, string>}>> $plan */
    private function apply(array $plan): void
    {
        $updated = 0;

        DB::transaction(function () use ($plan, &$updated): void {
            foreach ($plan as $table => $records) {
                foreach ($records as $id => $record) {
                    DB::table($table)->where('id', $id)->update($record['changes']);
                    $updated++;
                }
            }
        });

        $this->info("Готово: оновлено {$updated} записів.");
        $this->comment('Slug не змінювався — URL лишилися ті самі.');
    }
}
