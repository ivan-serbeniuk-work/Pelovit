<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        // Скільки товарів у кожній категорії — щоб вітрина не вела в порожню
        // видачу. У батьківської рахуємо разом із дочірніми.
        $counts = Product::where('is_active', true)
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($categories as $category) {
            $own = (int) ($counts[$category->id] ?? 0);
            $children = $category->children->sum(fn ($child) => (int) ($counts[$child->id] ?? 0));

            $category->setAttribute('products_count', $own + $children);
            $category->children->each(
                fn ($child) => $child->setAttribute('products_count', (int) ($counts[$child->id] ?? 0))
            );
        }

        $categoryIds = [];
        if ($request->category) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $categoryIds = $cat->children->pluck('id')->push($cat->id)->toArray();
            }
        }

        $products = Product::where('is_active', true)
            ->with('category')
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->search($request->query('q'))
            ->when($request->brand, fn ($q, $brand) => $q->where('brand', $brand))
            ->when($request->min_price, fn ($q, $min) => $q->where('price', '>=', $min))
            ->when($request->max_price, fn ($q, $max) => $q->where('price', '<=', $max))
            ->when($request->boolean('on_sale'), fn ($q) => $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price'))
            // Явний вибір користувача головніший за релевантність; якщо
            // сортування не задано, але є пошук — сортуємо за збігом.
            // Без жодного ORDER BY (як було раніше) порядок визначало
            // сховище, і той самий товар міг з'явитися на двох сторінках.
            ->when(
                $request->sort,
                function ($q, $sort) {
                    match ($sort) {
                        'price_asc' => $q->orderBy('price'),
                        'price_desc' => $q->orderByDesc('price'),
                        'new' => $q->orderByDesc('created_at'),
                        default => $q->orderByDesc('is_featured'),
                    };

                    $q->orderBy('id');
                },
                fn ($q) => $request->filled('q')
                    ? $q->orderByRelevance($request->query('q'))
                    : $q->orderByDesc('is_featured')->orderBy('id'),
            )
            ->paginate(12)
            ->withQueryString();

        $brands = Product::where('is_active', true)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        // Реальні межі цін — підказка під полями «від / до».
        // Нульові ціни (товар-подарунок) до мінімуму не беремо.
        $priceRange = Product::where('is_active', true)
            ->where('price', '>', 0)
            ->selectRaw('MIN(price) as min, MAX(price) as max')
            ->first();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'price_range' => [
                'min' => (int) floor((float) ($priceRange->min ?? 0)),
                'max' => (int) ceil((float) ($priceRange->max ?? 0)),
            ],
            // (object) обовʼязково: порожній PHP-масив серіалізується в JSON як
            // [], і на вітрині filters.sort потрапляє в Array.prototype.sort —
            // тобто «фільтр» виглядає застосованим, хоча його немає.
            'filters' => (object) $request->only(['category', 'sort', 'brand', 'min_price', 'max_price', 'q']),
        ]);
    }
}
