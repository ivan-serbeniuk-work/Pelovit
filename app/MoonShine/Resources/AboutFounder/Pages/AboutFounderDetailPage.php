<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutFounder\Pages;

use App\MoonShine\Resources\AboutFounder\AboutFounderResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<AboutFounderResource>
 */
class AboutFounderDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Ім\'я', 'name'),
            Text::make('Посада', 'role'),
            Textarea::make('Цитата', 'quote'),
            Switcher::make('Показувати', 'is_active'),
        ];
    }
}
