<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutValue;

use App\Models\AboutValue;
use App\MoonShine\Resources\AboutValue\Pages\AboutValueDetailPage;
use App\MoonShine\Resources\AboutValue\Pages\AboutValueFormPage;
use App\MoonShine\Resources\AboutValue\Pages\AboutValueIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutValue, AboutValueIndexPage, AboutValueFormPage, AboutValueDetailPage>
 */
class AboutValueResource extends ModelResource
{
    protected string $model = AboutValue::class;

    protected string $title = 'Цінності «Про нас»';

    protected string $column = 'title';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutValueIndexPage::class,
            AboutValueFormPage::class,
            AboutValueDetailPage::class,
        ];
    }
}
