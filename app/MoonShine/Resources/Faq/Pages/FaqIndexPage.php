<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Faq\Pages;

use App\Models\Faq;
use App\MoonShine\Resources\Faq\FaqResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<FaqResource>
 */
class FaqIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Select::make('Сторінка', 'page')->options(Faq::PAGES)->sortable(),
            Text::make('Питання', 'question'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активне', 'is_active')->updateOnPreview(),
        ];
    }
}
