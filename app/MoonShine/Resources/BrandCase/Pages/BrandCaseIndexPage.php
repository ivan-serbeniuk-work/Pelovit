<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\BrandCase\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\BrandCase\BrandCaseResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<BrandCaseResource>
 */
class BrandCaseIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Бренд', 'brand_name'),
            Text::make('Клієнт', 'client_name'),
            Text::make('Результат', 'result'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активний', 'is_active')->updateOnPreview(),
        ];
    }
}
