<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutSection\Pages;

use App\MoonShine\Resources\AboutSection\AboutSectionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<AboutSectionResource>
 */
class AboutSectionDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Назва', 'title'),
            Textarea::make('Текст', 'text'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Показувати', 'is_active'),
        ];
    }
}
