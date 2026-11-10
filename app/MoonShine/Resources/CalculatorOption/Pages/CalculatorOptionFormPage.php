<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption\Pages;

use App\Models\CalculatorOption;
use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use App\Support\CalculatorExplainer;
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

/**
 * @extends FormPage<CalculatorOptionResource>
 */
class CalculatorOptionFormPage extends FormPage
{
    use ValidatesUploadedImages;

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
                    ->hint($this->valueHint())
                    ->required(),
                Image::make('Зображення', 'image')->dir('images')->disk('public_root')
                    ->hint('Лише для тари — показується в калькуляторі.'),
                Text::make('Підказка', 'hint'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активна', 'is_active'),
            ]),
        ];
    }

    /**
     * Одиниця «значення» залежить від групи, тому підказка перелічує всі —
     * інакше в полі стоїть число без жодного натяку, що воно означає.
     */
    private function valueHint(): string
    {
        $parts = [];

        foreach (CalculatorOption::GROUPS as $group => $label) {
            $name = trim((string) preg_replace('/\s*\(.*\)$/u', '', $label));
            $parts[] = $name.' — '.CalculatorExplainer::VALUE_MEANING[$group][0];
        }

        return implode('; ', $parts)
            .'. Формула й приклад розрахунку — у блоці «Як працює калькулятор» у списку опцій.';
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'group' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'image' => $this->imageRules(),
        ];
    }
}
