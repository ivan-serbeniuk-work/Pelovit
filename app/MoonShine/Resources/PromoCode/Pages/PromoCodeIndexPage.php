<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCode\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<PromoCodeResource>
 */
class PromoCodeIndexPage extends BaseIndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Код', 'code'),
            Text::make('Тип', 'type')->badge(fn ($value) => $value === 'percent' ? 'green' : 'blue'),
            Text::make('Знижка', 'discount_label'),
            Text::make('Використано', 'usage_label'),
            Date::make('Діє до', 'expires_at')->format('d.m.Y'),
            Switcher::make('Активний', 'is_active')->updateOnPreview(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Активні', fn ($q) => $q->where('is_active', true)),
            QueryTag::make('Неактивні', fn ($q) => $q->where('is_active', false)),
        ];
    }
}
