<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\BrandCase;

use App\Models\BrandCase;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\BrandCase\Pages\BrandCaseDetailPage;
use App\MoonShine\Resources\BrandCase\Pages\BrandCaseFormPage;
use App\MoonShine\Resources\BrandCase\Pages\BrandCaseIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<BrandCase, BrandCaseIndexPage, BrandCaseFormPage, BrandCaseDetailPage>
 */
class BrandCaseResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['image'];

    protected string $model = BrandCase::class;

    protected string $title = 'Кейси: готові бренди';

    protected string $column = 'brand_name';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            BrandCaseIndexPage::class,
            BrandCaseFormPage::class,
            BrandCaseDetailPage::class,
        ];
    }
}
