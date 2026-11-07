<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Testimonial\Pages;

use App\MoonShine\Resources\Testimonial\TestimonialResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<TestimonialResource>
 */
class TestimonialDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Цитата', 'quote'),
            Textarea::make('Текст відгуку', 'text'),
            Text::make('Автор', 'author_name'),
            Text::make('Роль', 'author_role'),
            Image::make('Фото', 'image')->dir('images')->disk('public_root'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активний', 'is_active'),
        ];
    }
}
