<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Lead\Pages;

use App\MoonShine\Resources\Lead\LeadResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends DetailPage<LeadResource>
 */
class LeadDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Ім\'я', 'name'),
            Text::make('Телефон', 'phone'),
            Select::make('Спосіб зв\'язку', 'contact_method')
                ->options(['call' => 'Дзвінок', 'telegram' => 'Telegram', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp']),
            Text::make('Компанія', 'company'),
            Textarea::make('Деталі заявки', 'details'),
            Select::make('Джерело', 'source')
                ->options(['home' => 'Головна', 'contacts' => 'Контакти', 'masters' => 'Майстрам', 'opt' => 'Опт', 'contract' => 'Контрактне', 'contract-calculator' => 'Контрактне (калькулятор)', 'contract-tester' => 'Контрактне (тестер)']),
            Select::make('Статус', 'status')
                ->options(['new' => 'Новий', 'in_progress' => 'В роботі', 'done' => 'Завершено']),
            Date::make('Дата', 'created_at')->format('d.m.Y H:i'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function modifyDetailComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [...parent::topLayer()];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [...parent::mainLayer()];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [...parent::bottomLayer()];
    }
}
