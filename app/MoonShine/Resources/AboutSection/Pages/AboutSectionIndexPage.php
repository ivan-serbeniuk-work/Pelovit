<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSection\Pages;

use App\MoonShine\Resources\AboutSection\AboutSectionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<AboutSectionResource>
 */
class AboutSectionIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Назва', 'title'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Показувати', 'is_active')->updateOnPreview(),
        ];
    }
}
