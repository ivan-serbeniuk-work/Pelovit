<?php

declare(strict_types=1);

namespace App\MoonShine\Concerns;

use App\Services\UploadedImageOptimizer;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;

/**
 * Підмішується в ресурси з картинками: після збереження запису великі
 * завантажені фото зменшуються до розумної ширини (див. UploadedImageOptimizer).
 *
 * Ресурс перелічує свої колонки:
 *
 *     protected array $imageColumns = ['image'];
 *     protected array $imageGalleryColumns = ['images'];
 *     protected array $imageJsonColumns = ['videos' => 'poster'];
 *
 * Останній — для картинок усередині json-колонки з рядками-об'єктами
 * (постер відео лежить саме так), інакше такий файл повз оптимізатор.
 */
trait OptimizesUploadedImages
{
    protected function afterCreated(DataWrapperContract $item): DataWrapperContract
    {
        $this->optimizeImages($item);

        return parent::afterCreated($item);
    }

    protected function afterUpdated(DataWrapperContract $item): DataWrapperContract
    {
        $this->optimizeImages($item);

        return parent::afterUpdated($item);
    }

    private function optimizeImages(DataWrapperContract $item): void
    {
        $model = $item->getOriginal();
        $optimizer = app(UploadedImageOptimizer::class);

        foreach ($this->imageColumns ?? [] as $column) {
            $optimizer->optimize($model->{$column} ?? null, $this->imageDisk());
        }

        foreach ($this->imageGalleryColumns ?? [] as $column) {
            $value = $model->{$column} ?? null;

            if (\is_array($value)) {
                $optimizer->optimizeMany($value, $this->imageDisk());
            }
        }

        foreach ($this->imageJsonColumns ?? [] as $column => $key) {
            $rows = $model->{$column} ?? null;

            if (! \is_array($rows)) {
                continue;
            }

            $optimizer->optimizeMany(
                array_filter(array_column($rows, $key)),
                $this->imageDisk(),
            );
        }
    }

    private function imageDisk(): string
    {
        return $this->imageDisk ?? 'public_root';
    }
}
