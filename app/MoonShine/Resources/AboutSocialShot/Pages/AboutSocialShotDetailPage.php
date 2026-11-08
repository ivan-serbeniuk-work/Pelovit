<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSocialShot\Pages;

use App\MoonShine\Resources\AboutSocialShot\AboutSocialShotResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;

/**
 * @extends DetailPage<AboutSocialShotResource>
 */
class AboutSocialShotDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Image::make('Фото', 'image')->dir('images')->disk('public_root'),
            Text::make('Опис (alt)', 'alt'),
            Url::make('Посилання', 'url'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активна', 'is_active')->updateOnPreview(),
        ];
    }
}
