<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption\Pages;

use App\Models\CalculatorOption;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<CalculatorOptionResource>
 */
class CalculatorOptionFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Опція калькулятора', [
                ID::make(),
                Select::make('Група', 'group')->options(CalculatorOption::GROUPS)->required(),
                Text::make('Назва', 'name')->required(),
                Number::make('Значення', 'value')
                    ->step(0.0001)
                    ->hint('Собівартість за 1 мл (вид продукту), множник (складність формули) або ціна за штуку (тара, етикетка, коробка).')
                    ->required(),
                Text::make('Шлях до зображення', 'image')
                    ->hint('Лише для тари, напр. images/cosmetic_shot.png'),
                Text::make('Підказка', 'hint'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активна', 'is_active'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'group' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
