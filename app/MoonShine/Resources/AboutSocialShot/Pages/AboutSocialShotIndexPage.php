<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSocialShot\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\AboutSocialShot\AboutSocialShotResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;

/**
 * @extends BaseIndexPage<AboutSocialShotResource>
 */
class AboutSocialShotIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

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
