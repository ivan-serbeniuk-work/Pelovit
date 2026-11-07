<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutMilestone\Pages;

use App\MoonShine\Resources\AboutMilestone\AboutMilestoneResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<AboutMilestoneResource>
 */
class AboutMilestoneDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Рік', 'year')->sortable(),
            Text::make('Подія', 'text'),
            Select::make('Ряд', 'placement')->options(['top' => 'Верхній', 'bottom' => 'Нижній']),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активна', 'is_active')->updateOnPreview(),
        ];
    }
}
