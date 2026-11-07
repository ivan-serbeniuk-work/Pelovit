<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\MoonShineUserRole\Pages;

use App\MoonShine\Pages\Crud\BaseIndexPage;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends BaseIndexPage<MoonShineUserRoleResource>
 */
final class MoonShineUserRoleIndexPage extends BaseIndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('moonshine::ui.resource.role_name'), 'name'),
        ];
    }
}
