<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutStat\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\AboutStat\AboutStatResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<AboutStatResource>
 */
class AboutStatIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Значення', 'value'),
            Text::make('Підпис', 'label'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активний', 'is_active')->updateOnPreview(),
        ];
    }
}
