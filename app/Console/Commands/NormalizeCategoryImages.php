<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Зводить шляхи картинок категорій до теки, яку очікує форма адмінки.
 *
 * Поле в CategoryFormPage колись мало dir('categories'), потім його
 * змінили на dir('images') — під решту наявних файлів. MoonShine будує
 * прев'ю як dir + значення (FileTrait::getPrependedDir), тож рядки, що
 * лишилися з «categories/…», перетворюються на «images/categories/…» і
 * в адмінці виглядають як биті картинки. На вітрині вони при цьому
 * показуються, бо там шлях береться як є — звідси й плутанина.
 *
 * Команда переносить файл у public/images і переписує значення.
 * Ідемпотентна: рядки, що вже починаються з «images/», не чіпає.
 */
class NormalizeCategoryImages extends Command
{
    protected $signature = 'media:normalize-category-images
                            {--dry-run : тільки показати, що змінилося б}';

    protected $description = 'Перенести картинки категорій у public/images і виправити шляхи';

    public function handle(): int
    {
        $disk = Storage::disk('public_root');
        $dryRun = (bool) $this->option('dry-run');
        $fixed = 0;

        foreach (DB::table('categories')->whereNotNull('image')->orderBy('id')->get(['id', 'image']) as $category) {
            $old = (string) $category->image;

            if ($old === '' || str_starts_with($old, 'images/')) {
                continue;
            }

            $new = 'images/'.basename($old);

            $this->line("  #{$category->id}: {$old} → {$new}");

            if ($dryRun) {
                $fixed++;

                continue;
            }

            // Файл переносимо лише якщо він справді лежить за старим
            // шляхом і за новим ще нічого немає — щоб не затерти чуже.
            if ($disk->exists($old) && ! $disk->exists($new)) {
                $disk->move($old, $new);
            }

            DB::table('categories')->where('id', $category->id)->update(['image' => $new]);
            $fixed++;
        }

        if ($fixed === 0) {
            $this->info('Усі шляхи вже в порядку.');

            return self::SUCCESS;
        }

        $this->info($dryRun ? "Знайдено записів: {$fixed} (нічого не записано)." : "Виправлено записів: {$fixed}.");

        return self::SUCCESS;
    }
}
