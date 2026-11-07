<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutMilestone;

use App\Models\AboutMilestone;
use App\MoonShine\Resources\AboutMilestone\Pages\AboutMilestoneDetailPage;
use App\MoonShine\Resources\AboutMilestone\Pages\AboutMilestoneFormPage;
use App\MoonShine\Resources\AboutMilestone\Pages\AboutMilestoneIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutMilestone, AboutMilestoneIndexPage, AboutMilestoneFormPage, AboutMilestoneDetailPage>
 */
class AboutMilestoneResource extends ModelResource
{
    protected string $model = AboutMilestone::class;

    protected string $title = 'Історія «Про нас»';

    protected string $column = 'year';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutMilestoneIndexPage::class,
            AboutMilestoneFormPage::class,
            AboutMilestoneDetailPage::class,
        ];
    }
}
