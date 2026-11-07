<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Subscriber\Pages;

use App\MoonShine\Resources\Subscriber\SubscriberResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<SubscriberResource>
 */
class SubscriberIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Email::make('Email', 'email')->sortable(),
            Text::make('Імʼя', 'name'),
            // Вітальний промокод, виданий за підписку. Показуємо, щоб підтримка
            // могла відповісти «який у мене код» без копання в promo_codes.
            Text::make('Промокод', 'welcome_promo_code')->badge('primary'),
            Switcher::make('Активний', 'is_active')->updateOnPreview(),
            Date::make('Підписався', 'created_at')->format('d.m.Y')->sortable(),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return new ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, [
            $this->modifyDeleteButton($this->getResource()->getDeleteButton(redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(), isAsync: $this->isAsync())),
            $this->modifyMassDeleteButton($this->getResource()->getMassDeleteButton(redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(), isAsync: $this->isAsync())),
        ]);
    }

    protected function topRightButtons(): ListOf
    {
        return new ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, [
            ActionButton::make('Завантажити CSV', route('admin.subscribers.export'))
                ->icon('arrow-down-tray')
                ->secondary(),
            ...parent::topRightButtons()->toArray(),
        ]);
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
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
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
