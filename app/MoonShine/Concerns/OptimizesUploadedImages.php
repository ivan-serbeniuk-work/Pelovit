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
    }

    private function imageDisk(): string
    {
        return $this->imageDisk ?? 'public_root';
    }
}
