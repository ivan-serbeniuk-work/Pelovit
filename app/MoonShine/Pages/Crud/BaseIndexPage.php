<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Crud;

use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Enums\ClickAction;

/**
 * Спільна основа для index-сторінок адмінки.
 *
 * Клік по будь-якій комірці рядка (назва, дата, будь-яка колонка) відкриває
 * форму редагування — не потрібно цілитися в маленький олівець праворуч.
 * Посилання, кнопки, чекбокси та перемикачі Switcher працюють як раніше:
 * MoonShine не перехоплює клік по a / button / input / select / label,
 * тому inline-редагування і виділення рядків не зламані.
 *
 * Чому не в modifyListComponent: половина сторінок уже перевизначає його
 * і не кличе parent (reorderable, columnSelection), тож clickAction там би
 * губився. Тут ми обгортаємо вже змінений наслідником компонент.
 *
 * @extends IndexPage<covariant \MoonShine\Crud\Resources\CrudResource>
 */
abstract class BaseIndexPage extends IndexPage
{
    protected function getItemsComponent(iterable $items, FieldsContract $fields): ComponentContract
    {
        $component = parent::getItemsComponent($items, $fields);

        if ($component instanceof TableBuilderContract) {
            return $component->clickAction(ClickAction::EDIT);
        }

        return $component;
    }
}
