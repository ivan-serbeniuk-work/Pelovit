<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_all_required_keys(): void
    {
        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonStructure(['promotions', 'allProducts', 'categories', 'latestPosts', 'banners']);
    }

    public function test_promotions_returns_only_featured_active_products(): void
    {
        Product::factory()->featured()->create();
        Product::factory()->featured()->inactive()->create();
        Product::factory()->create();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonCount(1, 'promotions');
    }

    public function test_all_products_excludes_inactive(): void
    {
        Product::factory(3)->create();
        Product::factory(2)->inactive()->create();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonCount(3, 'allProducts');
    }

    public function test_all_products_limited_to_eight(): void
    {
        Product::factory(10)->create();

        $response = $this->getJson('/api/home');

        $response->assertOk();
        $this->assertCount(8, $response->json('allProducts'));
    }

    public function test_categories_returns_only_root_active(): void
    {
        $root = Category::factory()->create();
        Category::factory()->child($root->id)->create();
        Category::factory()->inactive()->create();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonCount(1, 'categories');
    }

    public function test_latest_posts_limited_to_three_published(): void
    {
        Post::factory(5)->create();
        Post::factory(2)->unpublished()->create();

        $response = $this->getJson('/api/home');

        $response->assertOk();
        $this->assertCount(3, $response->json('latestPosts'));
    }

    public function test_banners_returns_only_active(): void
    {
        Banner::factory(2)->create();
        Banner::factory()->inactive()->create();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonCount(2, 'banners');
    }

    public function test_banners_ordered_by_sort_order(): void
    {
        Banner::factory()->create(['title' => 'Third', 'sort_order' => 3]);
        Banner::factory()->create(['title' => 'First', 'sort_order' => 1]);
        Banner::factory()->create(['title' => 'Second', 'sort_order' => 2]);

        $response = $this->getJson('/api/home');

        $titles = collect($response->json('banners'))->pluck('title')->all();
        $this->assertEquals(['First', 'Second', 'Third'], $titles);
    }

    public function test_product_discount_percent_is_appended(): void
    {
        Product::factory()->featured()->withDiscount()->create();

        $response = $this->getJson('/api/home');

        $product = $response->json('promotions.0');
        $this->assertArrayHasKey('discount_percent', $product);
        $this->assertNotNull($product['discount_percent']);
    }

    /**
     * Товар картки в слайді героя задається в банері; неактивний товар
     * не має приїжджати на головну навіть якщо його колись вибрали.
     */
    public function test_banner_carries_its_product(): void
    {
        $product = Product::factory()->create();
        Banner::factory()->create(['product_id' => $product->id, 'is_active' => true]);

        $banner = $this->getJson('/api/home')->assertOk()->json('banners.0');

        $this->assertSame($product->id, $banner['product']['id']);
    }

    public function test_inactive_banner_product_is_not_returned(): void
    {
        $product = Product::factory()->inactive()->create();
        Banner::factory()->create(['product_id' => $product->id, 'is_active' => true]);

        $banner = $this->getJson('/api/home')->assertOk()->json('banners.0');

        $this->assertNull($banner['product']);
    }
}
