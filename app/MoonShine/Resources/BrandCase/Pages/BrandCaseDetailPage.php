<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\BrandCase\Pages;

use App\MoonShine\Resources\BrandCase\BrandCaseResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<BrandCaseResource>
 */
class BrandCaseDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Назва бренду', 'brand_name'),
            Text::make('Ім\'я клієнта', 'client_name'),
            Text::make('Хто клієнт', 'client_role'),
            Textarea::make('Опис кейса', 'description'),
            Text::make('Результат', 'result'),
            Text::make('Шлях до фото', 'image'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активний', 'is_active'),
        ];
    }
}
