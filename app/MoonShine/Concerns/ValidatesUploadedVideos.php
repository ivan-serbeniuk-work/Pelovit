<?php

declare(strict_types=1);

namespace App\MoonShine\Concerns;

/**
 * Спільні правила для полів з відеофайлами — дзеркало
 * ValidatesUploadedImages, тільки для роликів.
 *
 * 50 МБ (51200 КБ) навмисно менше за ліміт nginx і php.ini (60 МБ):
 * запас потрібен на multipart-обгортку й постер у тій самій формі, щоб
 * завеликий файл ловила зрозуміла помилка валідації, а не 413 від nginx.
 *
 * mimetypes перевіряє реальний тип файлу, mimes — розширення: одного
 * тільки розширення мало, перейменований .exe теж був би «mp4».
 */
trait ValidatesUploadedVideos
{
    /**
     * @return list<string>
     */
    protected function videoRules(): array
    {
        return ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'mimes:mp4,webm', 'max:51200'];
    }
}
