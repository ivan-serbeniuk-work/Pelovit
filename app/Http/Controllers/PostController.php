<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()->orderByDesc('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $posts = $query->paginate(9)->withQueryString();
        $featured = Post::published()->where('is_featured', true)->orderByDesc('published_at')->limit(2)->get();
        $categories = Post::published()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return Inertia::render('Journal/Index', compact('posts', 'featured', 'categories'));
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return Inertia::render('Journal/Show', compact('post', 'related'));
    }
}
