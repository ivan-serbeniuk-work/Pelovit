<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutStat\Pages;

use App\MoonShine\Resources\AboutStat\AboutStatResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<AboutStatResource>
 */
class AboutStatIndexPage extends IndexPage
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
