<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Переносить підкатегорії з однієї категорії в іншу й, за потреби,
 * видаляє порожнього батька.
 *
 * Навіщо окрема команда, а не дві кнопки в адмінці: у схемі
 * products.category_id стоїть cascadeOnDelete, тобто видалення
 * категорії з товарами мовчки стирає самі товари. А parent_id має
 * nullOnDelete — видалення батька робить його дітей кореневими, і
 * каталог (він показує лише корені) раптом розростається вдвічі.
 * Команда робить обидва кроки в правильному порядку й відмовляється
 * працювати там, де можна втратити товари.
 */
class MoveCategoryChildren extends Command
{
    protected $signature = 'categories:move-children
                            {from : id або slug категорії, чиїх дітей переносимо}
                            {to : id або slug нового батька}
                            {--delete-source : видалити вихідну категорію після переносу}
                            {--dry-run : тільки показати, що станеться}';

    protected $description = 'Перенести підкатегорії до іншої категорії (і прибрати стару)';

    public function handle(): int
    {
        $from = $this->resolve((string) $this->argument('from'));
        $to = $this->resolve((string) $this->argument('to'));

        if ($from === null || $to === null) {
            $this->error('Категорію не знайдено: перевір id або slug.');

            return self::FAILURE;
        }

        if ($from->is($to)) {
            $this->error('Вихідна й цільова категорії збігаються.');

            return self::FAILURE;
        }

        $children = Category::where('parent_id', $from->id)->orderBy('sort_order')->get();
        $ownProducts = Product::where('category_id', $from->id)->count();

        $this->line("Переносимо з «{$from->name}» (#{$from->id}) до «{$to->name}» (#{$to->id})");
        $this->line('Підкатегорій: '.$children->count().($children->isEmpty() ? '' : ' — '.$children->pluck('name')->implode(', ')));
        $this->line("Товарів прямо у вихідній категорії: {$ownProducts}");

        // Головна перевірка: cascadeOnDelete забрав би ці товари разом
        // з категорією, тож видаляти її з товарами ми не дамо.
        if ($this->option('delete-source') && $ownProducts > 0) {
            $this->error("У категорії {$ownProducts} товарів — видалення стерло б їх. Спершу перенеси товари.");

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->comment('Пробний запуск — нічого не змінено.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($children, $from, $to): void {
            foreach ($children as $child) {
                $child->update(['parent_id' => $to->id]);
            }

            if ($this->option('delete-source')) {
                // Перестраховка всередині транзакції: якщо між перевіркою
                // і цим рядком щось з'явилося — відкотимося.
                if (Product::where('category_id', $from->id)->exists()) {
                    throw new \RuntimeException('У категорії з’явилися товари — видалення скасовано.');
                }

                $from->delete();
            }
        });

        $this->info('Перенесено підкатегорій: '.$children->count());

        if ($this->option('delete-source')) {
            $this->info("Категорію «{$from->name}» видалено.");
        }

        return self::SUCCESS;
    }

    private function resolve(string $key): ?Category
    {
        return ctype_digit($key)
            ? Category::find((int) $key)
            : Category::where('slug', $key)->first();
    }
}
