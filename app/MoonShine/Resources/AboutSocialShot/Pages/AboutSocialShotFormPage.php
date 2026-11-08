<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSocialShot\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\AboutSocialShot\AboutSocialShotResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;

/**
 * @extends FormPage<AboutSocialShotResource>
 */
class AboutSocialShotFormPage extends FormPage
{
    use ValidatesUploadedImages;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Image::make('Фото', 'image')->dir('images')->disk('public_root')->required(),
                Url::make('Посилання на допис', 'url')
                    ->hint('Порожнє — плитка веде на профіль із налаштувань сайту.'),
                Text::make('Опис (alt)', 'alt')
                    ->hint('Що на фото: читають пошукові системи та екранні читалки.'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активна', 'is_active'),
            ]),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'image' => $this->imageRules(),
            'url' => ['nullable', 'url', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
