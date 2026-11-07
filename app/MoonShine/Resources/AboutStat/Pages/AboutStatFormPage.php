<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutStat\Pages;

use App\MoonShine\Resources\AboutStat\AboutStatResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<AboutStatResource>
 */
class AboutStatFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Значення', 'value')->required()
                    ->hint('Рядком, бо в макеті є «40+»'),
                Text::make('Підпис', 'label')->required(),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активний', 'is_active'),
            ]),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'value' => ['required', 'string', 'max:16'],
            'label' => ['required', 'string', 'max:255'],
        ];
    }
}
