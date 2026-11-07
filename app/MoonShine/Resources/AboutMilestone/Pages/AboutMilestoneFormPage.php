<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutMilestone\Pages;

use App\MoonShine\Resources\AboutMilestone\AboutMilestoneResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<AboutMilestoneResource>
 */
class AboutMilestoneFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Рік', 'year')->required(),
                Textarea::make('Подія', 'text')->required(),
                Image::make('Фото', 'image')->dir('about')->disk('public_root'),
                Select::make('Ряд', 'placement')
                    ->options(['top' => 'Верхній', 'bottom' => 'Нижній'])->required()
                    ->hint('У верхньому ряду фото зверху, у нижньому — знизу (як у макеті).'),
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
            'year' => ['required', 'string', 'max:8'],
            'text' => ['required', 'string'],
            'placement' => ['required', 'in:top,bottom'],
        ];
    }
}
