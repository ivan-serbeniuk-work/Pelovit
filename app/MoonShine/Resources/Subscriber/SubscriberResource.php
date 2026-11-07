<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Subscriber;

use App\Models\Subscriber;
use App\MoonShine\Resources\Subscriber\Pages\SubscriberDetailPage;
use App\MoonShine\Resources\Subscriber\Pages\SubscriberFormPage;
use App\MoonShine\Resources\Subscriber\Pages\SubscriberIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Subscriber, SubscriberIndexPage, SubscriberFormPage, SubscriberDetailPage>
 */
class SubscriberResource extends ModelResource
{
    protected string $model = Subscriber::class;

    protected string $title = 'Subscribers';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SubscriberIndexPage::class,
            SubscriberFormPage::class,
            SubscriberDetailPage::class,
        ];
    }
}
