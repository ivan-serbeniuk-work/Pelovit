<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutFounder\Pages;

use App\MoonShine\Resources\AboutFounder\AboutFounderResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<AboutFounderResource>
 */
class AboutFounderIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Ім\'я', 'name'),
            Text::make('Посада', 'role'),
            Switcher::make('Показувати', 'is_active')->updateOnPreview(),
        ];
    }
}
