<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Testimonial;

use App\Models\Testimonial;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\Testimonial\Pages\TestimonialDetailPage;
use App\MoonShine\Resources\Testimonial\Pages\TestimonialFormPage;
use App\MoonShine\Resources\Testimonial\Pages\TestimonialIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Testimonial, TestimonialIndexPage, TestimonialFormPage, TestimonialDetailPage>
 */
class TestimonialResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['image'];

    protected string $model = Testimonial::class;

    protected string $title = 'Відгуки про співпрацю';

    protected string $column = 'author_name';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            TestimonialIndexPage::class,
            TestimonialFormPage::class,
            TestimonialDetailPage::class,
        ];
    }
}
