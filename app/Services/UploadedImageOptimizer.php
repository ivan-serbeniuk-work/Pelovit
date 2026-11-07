<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Стискає картинки, завантажені через адмінку.
 *
 * Ліміт завантаження — 20 МБ, але класти на сайт фото в 20 МБ не варто:
 * на проді всього 20 ГБ диска, і він уже одного разу забивався. Тому все,
 * що ширше за 1920 px, зменшуємо й перезберігаємо на місці.
 *
 * Навмисно на GD (розширення вже стоїть) — щоб не тягнути в проєкт
 * intervention/image заради одного ресайзу.
 */
class UploadedImageOptimizer
{
    public function __construct(
        private readonly int $maxSide = 1920,
        private readonly int $quality = 85,
    ) {}

    /**
     * Шлях — відносний для диска, тобто такий, як лежить у БД
     * («images/foo.png»). Будь-яка несподіванка (не картинка, зламаний
     * файл, екзотичний формат) — просто лишаємо файл як є: збереження
     * запису в адмінці важливіше за стиснення.
     */
    public function optimize(?string $path, string $disk = 'public_root'): bool
    {
        if (blank($path)) {
            return false;
        }

        try {
            $storage = Storage::disk($disk);

            if (! $storage->exists($path)) {
                return false;
            }

            $absolute = $storage->path($path);
            $info = @getimagesize($absolute);

            if ($info === false) {
                return false;
            }

            [$width, $height] = $info;
            $type = $info[2];

            if (max($width, $height) <= $this->maxSide) {
                return false;
            }

            $source = @imagecreatefromstring((string) file_get_contents($absolute));

            if ($source === false) {
                return false;
            }

            $scale = $this->maxSide / max($width, $height);
            $target = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));

            // PNG і WEBP можуть бути з прозорістю: без цього вона стане чорною.
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            }

            imagecopyresampled(
                $target, $source,
                0, 0, 0, 0,
                imagesx($target), imagesy($target), $width, $height,
            );

            $saved = match ($type) {
                IMAGETYPE_PNG => imagepng($target, $absolute, 8),
                IMAGETYPE_WEBP => imagewebp($target, $absolute, $this->quality),
                IMAGETYPE_JPEG => imagejpeg($target, $absolute, $this->quality),
                default => false,
            };

            imagedestroy($source);
            imagedestroy($target);

            return $saved;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Для полів-галерей, де в колонці лежить масив шляхів.
     *
     * @param  iterable<array-key, string|null>  $paths
     */
    public function optimizeMany(iterable $paths, string $disk = 'public_root'): void
    {
        foreach ($paths as $path) {
            $this->optimize(\is_string($path) ? $path : null, $disk);
        }
    }
}
