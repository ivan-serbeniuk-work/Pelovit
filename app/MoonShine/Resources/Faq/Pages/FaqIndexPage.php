<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Faq\Pages;

use App\Models\Faq;
use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\Faq\FaqResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<FaqResource>
 */
class FaqIndexPage extends BaseIndexPage
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
