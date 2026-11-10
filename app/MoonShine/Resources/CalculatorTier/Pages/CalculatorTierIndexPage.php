<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorTier\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use App\Support\CalculatorExplainer;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;
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

    /**
     * @return list<ComponentContract>
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
            Box::make('Як працює калькулятор', [
                FlexibleRender::make(app(CalculatorExplainer::class)->render()),
            ]),
        ];
    }
}
