<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Прибирання за json-колонкою `videos`.
 *
 * MoonShine сам видаляє файл з диска, коли його знімають зі звичайного
 * поля File (див. FileModelApply::removeExcludedFiles), але вкладене в
 * Json поле проходить іншим шляхом: у базі лишається рядок із порожнім
 * file, а сам ролик назавжди висить у public/videos. На 50 МБ за штуку
 * це швидко стає проблемою диска.
 *
 * Тому тут: порожні рядки викидаємо, а файли, на які більше ніхто не
 * посилається, прибираємо з диска після вдалого збереження.
 */
trait CleansUpVideoFiles
{
    /** @var list<string> */
    private array $videoFilesToDelete = [];

    public static function bootCleansUpVideoFiles(): void
    {
        static::saving(function (self $model): void {
            $old = static::videoPaths($model->getOriginal('videos'));

            $rows = array_values(array_filter(
                (array) $model->videos,
                static fn ($row): bool => ! empty($row['file']),
            ));

            $model->videos = $rows === [] ? null : $rows;

            $model->videoFilesToDelete = array_diff($old, static::videoPaths($rows));
        });

        static::saved(function (self $model): void {
            foreach ($model->videoFilesToDelete as $path) {
                Storage::disk('public_root')->delete($path);
            }

            $model->videoFilesToDelete = [];
        });
    }

    /**
     * Усі шляхи (ролик + постер) із рядків колонки.
     *
     * @return list<string>
     */
    private static function videoPaths(mixed $rows): array
    {
        if (\is_string($rows)) {
            $rows = json_decode($rows, true);
        }

        $paths = [];

        foreach ((array) $rows as $row) {
            foreach (['file', 'poster'] as $key) {
                if (! empty($row[$key])) {
                    $paths[] = $row[$key];
                }
            }
        }

        return $paths;
    }
}
