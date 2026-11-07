<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutStat;

use App\Models\AboutStat;
use App\MoonShine\Resources\AboutStat\Pages\AboutStatDetailPage;
use App\MoonShine\Resources\AboutStat\Pages\AboutStatFormPage;
use App\MoonShine\Resources\AboutStat\Pages\AboutStatIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutStat, AboutStatIndexPage, AboutStatFormPage, AboutStatDetailPage>
 */
class AboutStatResource extends ModelResource
{
    protected string $model = AboutStat::class;

    protected string $title = 'Показники «Про нас»';

    protected string $column = 'label';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutStatIndexPage::class,
            AboutStatFormPage::class,
            AboutStatDetailPage::class,
        ];
    }
}
