<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption\Pages;

use App\Models\CalculatorOption;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<CalculatorOptionResource>
 */
class CalculatorOptionDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Select::make('Група', 'group')->options(CalculatorOption::GROUPS),
            Text::make('Назва', 'name'),
            Number::make('Значення', 'value'),
            Image::make('Зображення', 'image')->dir('images')->disk('public_root'),
            Text::make('Підказка', 'hint'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активна', 'is_active'),
        ];
    }
}
