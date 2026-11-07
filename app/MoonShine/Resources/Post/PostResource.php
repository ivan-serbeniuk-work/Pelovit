<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post;

use App\Models\Post;
use App\MoonShine\Resources\Post\Pages\PostDetailPage;
use App\MoonShine\Resources\Post\Pages\PostFormPage;
use App\MoonShine\Resources\Post\Pages\PostIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * @extends ModelResource<Post, PostIndexPage, PostFormPage, PostDetailPage>
 */
class PostResource extends ModelResource
{
    protected string $model = Post::class;

    protected string $title = 'Журнал';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            PostIndexPage::class,
            PostFormPage::class,
            PostDetailPage::class,
        ];
    }
}
