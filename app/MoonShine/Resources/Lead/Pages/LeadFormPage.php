<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Lead\Pages;

use App\MoonShine\Resources\Lead\LeadResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends FormPage<LeadResource>
 */
class LeadFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Заявка', [
                ID::make(),
                Text::make('Ім\'я', 'name')->required(),
                Phone::make('Телефон', 'phone')->required(),
                Select::make('Спосіб зв\'язку', 'contact_method')
                    ->options(['call' => 'Дзвінок', 'telegram' => 'Telegram', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp'])
                    ->required(),
                Text::make('Компанія', 'company'),
                Textarea::make('Деталі заявки', 'details'),
                Select::make('Джерело', 'source')
                    ->options(['home' => 'Головна', 'contacts' => 'Контакти', 'masters' => 'Майстрам', 'opt' => 'Опт', 'contract' => 'Контрактне', 'contract-calculator' => 'Контрактне (калькулятор)', 'contract-tester' => 'Контрактне (тестер)'])
                    ->required(),
                Select::make('Статус', 'status')
                    ->options(['new' => 'Новий', 'in_progress' => 'В роботі', 'done' => 'Завершено'])
                    ->required(),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
