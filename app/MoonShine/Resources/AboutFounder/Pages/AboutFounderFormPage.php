<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\AboutFounder\Pages;

use App\MoonShine\Concerns\ValidatesUploadedImages;
use App\MoonShine\Resources\AboutFounder\AboutFounderResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<AboutFounderResource>
 */
class AboutFounderFormPage extends FormPage
{
    use ValidatesUploadedImages;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Ім\'я та прізвище', 'name')->required(),
                Text::make('Посада', 'role'),
                Textarea::make('Цитата', 'quote')
                    ->hint('Лапки додаються в розмітці — тут їх писати не треба.'),
                Image::make('Фото', 'photo')->dir('images')->disk('public_root'),
                Switcher::make('Показувати', 'is_active')
                    ->hint('Вимкнено — картка засновника на сторінці не показується.'),
            ]),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'photo' => $this->imageRules(),
        ];
    }
}
