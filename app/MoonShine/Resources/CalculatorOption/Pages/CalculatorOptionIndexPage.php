<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption\Pages;

use App\Models\CalculatorOption;
use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<CalculatorOptionResource>
 */
class CalculatorOptionIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Select::make('Група', 'group')->options(CalculatorOption::GROUPS)->sortable(),
            Text::make('Назва', 'name'),
            Number::make('Значення', 'value')->step(0.0001),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активна', 'is_active')->updateOnPreview(),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Група', 'group')->options(CalculatorOption::GROUPS),
        ];
    }
}
