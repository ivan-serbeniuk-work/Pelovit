<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Testimonial\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\Testimonial\TestimonialResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<TestimonialResource>
 */
class TestimonialIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Автор', 'author_name'),
            Text::make('Роль', 'author_role'),
            Text::make('Цитата', 'quote'),
            Number::make('Порядок', 'sort_order')->sortable(),
            Switcher::make('Активний', 'is_active')->updateOnPreview(),
        ];
    }
}
