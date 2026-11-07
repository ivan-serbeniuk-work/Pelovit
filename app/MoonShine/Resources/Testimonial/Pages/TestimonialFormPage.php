<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Testimonial\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\Testimonial\TestimonialResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<TestimonialResource>
 */
class TestimonialFormPage extends FormPage
{
    use ValidatesUploadedImages;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Відгук', [
                ID::make(),
                Text::make('Цитата', 'quote')->hint('Короткий заголовок відгуку в лапках.')->required(),
                Textarea::make('Текст відгуку', 'text')->required(),
                Text::make('Автор', 'author_name')->required(),
                Text::make('Роль', 'author_role')->hint('Напр. косметолог, майстер манікюру'),
                Image::make('Фото', 'image')->dir('images')->disk('public_root')
                    ->hint('Фото автора відгуку.'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активний', 'is_active'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'quote' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string'],
            'author_name' => ['required', 'string', 'max:255'],
            'image' => $this->imageRules(),
        ];
    }
}
