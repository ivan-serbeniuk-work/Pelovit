<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->firstOrFail();

        [$related, $relatedSource] = $this->related($product);

        return response()->json([
            ...compact('product', 'related'),
            // Вітрина за цим вибирає заголовок: «часто купують» чесне лише
            // для ручної добірки, для сусідів по категорії це «схожі».
            'related_source' => $relatedSource,
        ]);
    }

    /**
     * Супутні товари: спочатку те, що вибрали руками в адмінці, і лише
     * якщо там порожньо — сусіди по категорії.
     *
     * Запасний варіант сортуємо: спершу обрані (is_featured), далі нові.
     * Раніше вибірка йшла без сортування, тобто за id — на сторінці
     * щоразу висіли чотири найстаріші товари категорії.
     */
    /**
     * @return array{0: Collection, 1: 'manual'|'category'}
     */
    private function related(Product $product): array
    {
        $manual = $product->related()
            ->where('is_active', true)
            ->with('category')
            ->limit(4)
            ->get();

        if ($manual->isNotEmpty()) {
            return [$manual, 'manual'];
        }

        $fallback = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return [$fallback, 'category'];
    }
}
