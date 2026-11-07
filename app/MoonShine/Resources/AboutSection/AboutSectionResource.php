<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSection;

use App\Models\AboutSection;
use App\MoonShine\Resources\AboutSection\Pages\AboutSectionDetailPage;
use App\MoonShine\Resources\AboutSection\Pages\AboutSectionFormPage;
use App\MoonShine\Resources\AboutSection\Pages\AboutSectionIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutSection, AboutSectionIndexPage, AboutSectionFormPage, AboutSectionDetailPage>
 */
class AboutSectionResource extends ModelResource
{
    protected string $model = AboutSection::class;

    protected string $title = 'Вкладки «Про нас»';

    protected string $column = 'title';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutSectionIndexPage::class,
            AboutSectionFormPage::class,
            AboutSectionDetailPage::class,
        ];
    }
}
