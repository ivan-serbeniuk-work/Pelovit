<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Підказки під полем пошуку в шапці.
 *
 * Відповідь навмисно худа: це не каталог, а кілька рядків у випадайці,
 * і вона смикається на кожне натискання клавіші (з дебаунсом на вітрині).
 */
class SearchApiController extends Controller
{
    /** Скільки рядків показуємо у випадайці. */
    private const PRODUCT_LIMIT = 6;

    private const CATEGORY_LIMIT = 3;

    /** Коротший запит дає сміттєву видачу — у базу навіть не ходимо. */
    private const MIN_QUERY_LENGTH = 2;

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));

        if (mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            // Саме 200 з порожніми списками, а не 422: вітрині не треба
            // розрізняти два формати відповіді.
            return response()->json(['q' => $q, 'products' => [], 'categories' => [], 'total' => 0]);
        }

        $matches = Product::where('is_active', true)->search($q);

        $products = (clone $matches)
            ->orderByRelevance($q)
            ->limit(self::PRODUCT_LIMIT)
            ->get(['id', 'name', 'slug', 'image', 'price', 'old_price'])
            // youtube_id у $appends тягнув би video_url, якого тут немає,
            // і додавав у відповідь порожнє поле без жодної користі.
            ->each->setAppends(['discount_percent']);

        $categories = Category::where('is_active', true)
            ->search($q)
            ->orderBy('sort_order')
            ->limit(self::CATEGORY_LIMIT)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'q' => $q,
            'products' => $products,
            'categories' => $categories,
            // Повна кількість — для рядка «Показати всі результати (N)».
            'total' => (clone $matches)->count(),
        ]);
    }
}
