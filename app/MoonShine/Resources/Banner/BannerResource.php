<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Banner;

use App\Models\Banner;
use App\MoonShine\Resources\Banner\Pages\BannerDetailPage;
use App\MoonShine\Resources\Banner\Pages\BannerFormPage;
use App\MoonShine\Resources\Banner\Pages\BannerIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Banner, BannerIndexPage, BannerFormPage, BannerDetailPage>
 */
class BannerResource extends ModelResource
{
    protected string $model = Banner::class;

    protected string $title = 'Banners';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            BannerIndexPage::class,
            BannerFormPage::class,
            BannerDetailPage::class,
        ];
    }
}
