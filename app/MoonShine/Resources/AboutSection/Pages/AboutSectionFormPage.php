<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSection\Pages;

use App\MoonShine\Resources\AboutSection\AboutSectionResource;
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
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<AboutSectionResource>
 */
class AboutSectionFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Назва вкладки', 'title')->required(),
                Textarea::make('Текст', 'text')
                    ->hint('Показується праворуч, коли вкладку вибрано.'),
                Image::make('Фото', 'image')->dir('about')->disk('public_root'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Показувати', 'is_active')
                    ->hint('Вимкнена вкладка не показується на сайті зовсім.'),
            ]),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
