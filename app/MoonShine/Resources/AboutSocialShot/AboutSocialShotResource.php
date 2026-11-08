<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSocialShot;

use App\Models\AboutSocialShot;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\AboutSocialShot\Pages\AboutSocialShotDetailPage;
use App\MoonShine\Resources\AboutSocialShot\Pages\AboutSocialShotFormPage;
use App\MoonShine\Resources\AboutSocialShot\Pages\AboutSocialShotIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<AboutSocialShot, AboutSocialShotIndexPage, AboutSocialShotFormPage, AboutSocialShotDetailPage>
 */
class AboutSocialShotResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['image'];

    protected string $model = AboutSocialShot::class;

    protected string $title = 'Слідкуйте за нами';

    protected string $column = 'alt';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            AboutSocialShotIndexPage::class,
            AboutSocialShotFormPage::class,
            AboutSocialShotDetailPage::class,
        ];
    }
}
