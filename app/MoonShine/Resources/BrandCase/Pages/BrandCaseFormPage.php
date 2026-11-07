<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\BrandCase\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\BrandCase\BrandCaseResource;
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
 * @extends FormPage<BrandCaseResource>
 */
class BrandCaseFormPage extends FormPage
{
    use ValidatesUploadedImages;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Кейс', [
                ID::make(),
                Text::make('Назва бренду', 'brand_name')->required(),
                Text::make('Ім\'я клієнта', 'client_name'),
                Text::make('Хто клієнт', 'client_role')->hint('Напр. майстер манікюру'),
                Textarea::make('Опис кейса', 'description'),
                Text::make('Результат', 'result')->hint('Напр. 500 шт за 15 днів'),
                Image::make('Фото', 'image')->dir('images')->disk('public_root')
                    ->hint('Фото готової продукції під брендом клієнта.'),
                Number::make('Порядок', 'sort_order'),
                Switcher::make('Активний', 'is_active'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'brand_name' => ['required', 'string', 'max:255'],
            'image' => $this->imageRules(),
        ];
    }
}
