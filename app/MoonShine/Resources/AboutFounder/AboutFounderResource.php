<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutFounder;

use App\Models\AboutFounder;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\AboutFounder\Pages\AboutFounderDetailPage;
use App\MoonShine\Resources\AboutFounder\Pages\AboutFounderFormPage;
use App\MoonShine\Resources\AboutFounder\Pages\AboutFounderIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutFounder, AboutFounderIndexPage, AboutFounderFormPage, AboutFounderDetailPage>
 */
class AboutFounderResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['photo'];

    protected string $model = AboutFounder::class;

    protected string $title = 'Засновник «Про нас»';

    protected string $column = 'name';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutFounderIndexPage::class,
            AboutFounderFormPage::class,
            AboutFounderDetailPage::class,
        ];
    }
}
