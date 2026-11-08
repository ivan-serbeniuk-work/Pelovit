<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class HomeApiController extends Controller
{
    public function index(): JsonResponse
    {
        $promotions = Product::where('is_active', true)
            ->where('is_featured', true)
            ->limit(4)
            ->with('category')
            ->get();

        $allProducts = Product::where('is_active', true)
            ->limit(8)
            ->with('category')
            ->get();

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $latestPosts = Post::published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        // product — картка в слайді героя; у кожного банера свій товар,
        // порожній лишає автовибір на боці вітрини.
        $banners = Banner::where('is_active', true)
            ->with(['product' => fn ($q) => $q->where('is_active', true)->with('category')])
            ->orderBy('sort_order')
            ->get();

        $faqs = Faq::active('home')->get(['id', 'question', 'answer']);

        return response()->json(compact('promotions', 'allProducts', 'categories', 'latestPosts', 'banners', 'faqs'));
    }
}
