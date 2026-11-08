<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use App\Models\Product;
use App\MoonShine\Concerns\OptimizesUploadedImages;
use App\MoonShine\Resources\Product\Pages\ProductDetailPage;
use App\MoonShine\Resources\Product\Pages\ProductFormPage;
use App\MoonShine\Resources\Product\Pages\ProductIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\AsyncMethod;

/**
 * @extends ModelResource<Product, ProductIndexPage, ProductFormPage, ProductDetailPage>
 */
class ProductResource extends ModelResource
{
    use OptimizesUploadedImages;

    /** @var list<string> Колонки з картинками — великі фото стискаються після збереження. */
    protected array $imageColumns = ['image'];

    /** @var list<string> Колонки-галереї: масив шляхів. */
    protected array $imageGalleryColumns = ['images'];

    /** @var array<string, string> Картинки всередині json: постер кожного ролика. */
    protected array $imageJsonColumns = ['videos' => 'poster'];

    protected string $model = Product::class;

    protected string $title = 'Товари';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ProductIndexPage::class,
            ProductFormPage::class,
            ProductDetailPage::class,
        ];
    }

    #[AsyncMethod]
    public function massActivate(): void
    {
        $ids = request()->get('ids', []);
        Product::whereIn('id', $ids)->update(['is_active' => true]);
        session()->put('toast', ['type' => 'success', 'message' => 'Товари активовано']);
    }

    #[AsyncMethod]
    public function massDeactivate(): void
    {
        $ids = request()->get('ids', []);
        Product::whereIn('id', $ids)->update(['is_active' => false]);
        session()->put('toast', ['type' => 'success', 'message' => 'Товари деактивовано']);
    }
}
