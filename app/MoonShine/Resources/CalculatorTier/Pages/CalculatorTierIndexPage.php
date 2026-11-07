<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorTier\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;

/**
 * @extends BaseIndexPage<CalculatorTierResource>
 */
class CalculatorTierIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Number::make('Від кількості, шт', 'min_quantity')->sortable(),
            Number::make('Знижка, %', 'discount_percent')->step(0.01),
        ];
    }
}
