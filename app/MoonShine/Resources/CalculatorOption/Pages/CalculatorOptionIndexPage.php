<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalculatorOption\Pages;

use App\Models\CalculatorOption;
use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use App\Support\CalculatorExplainer;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;
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
            // Саме число без одиниці нічого не говорить: 1.2 — це ₴ за мл,
            // а 1.35 — множник. Підпис бере одиницю з групи рядка.
            Text::make('Значення', 'value')
                ->changePreview(fn (mixed $value, mixed $field, CalculatorOption $item): string => trim(
                    rtrim(rtrim(number_format((float) $value, 4, '.', ' '), '0'), '.')
                    .' '.(CalculatorExplainer::VALUE_MEANING[$item->group][0] ?? '')
                )),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активна', 'is_active')->updateOnPreview(),
        ];
    }

    /**
     * Пояснення над таблицею: інакше з рядків «Крем 1.2» і «Базова формула 1»
     * зрозуміти, з чого складається ціна, можна лише читаючи код вітрини.
     *
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
