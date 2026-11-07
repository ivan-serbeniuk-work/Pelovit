<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorTier\Pages;

use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;

/**
 * @extends FormPage<CalculatorTierResource>
 */
class CalculatorTierFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Ступінь знижки', [
                ID::make(),
                Number::make('Від кількості, шт', 'min_quantity')
                    ->hint('Застосовується найвища ступінь, поріг якої пройдено.')
                    ->required(),
                Number::make('Знижка, %', 'discount_percent')->step(0.01)->required(),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'min_quantity' => ['required', 'integer', 'min:1'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
