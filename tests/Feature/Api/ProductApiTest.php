<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_product_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-krem']);

        $this->getJson('/api/products/test-krem')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'test-krem', 'id' => $product->id]);
    }

    public function test_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/products/ne-isnuie')
            ->assertNotFound();
    }

    public function test_inactive_product_returns_404(): void
    {
        Product::factory()->inactive()->create(['slug' => 'inactive-product']);

        $this->getJson('/api/products/inactive-product')
            ->assertNotFound();
    }

    public function test_response_includes_instruction(): void
    {
        $product = Product::factory()->create([
            'slug' => 'krem-z-instruktsiieiu',
            'instruction' => '<p>Склад та спосіб застосування</p>',
        ]);

        $this->getJson("/api/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('product.instruction', '<p>Склад та спосіб застосування</p>');
    }

    public function test_response_includes_related_products(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        Product::factory(3)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonStructure(['product', 'related']);
        $this->assertCount(3, $response->json('related'));
    }

    public function test_related_limited_to_four(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        Product::factory(6)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->slug}");

        $this->assertCount(4, $response->json('related'));
    }

    public function test_related_excludes_current_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        Product::factory(2)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->slug}");

        $relatedIds = collect($response->json('related'))->pluck('id')->all();
        $this->assertNotContains($product->id, $relatedIds);
    }

    public function test_discount_percent_calculated(): void
    {
        Product::factory()->withDiscount()->create(['slug' => 'sale-item']);

        $response = $this->getJson('/api/products/sale-item');

        $this->assertNotNull($response->json('product.discount_percent'));
        $this->assertGreaterThan(0, $response->json('product.discount_percent'));
    }

    public function test_no_discount_when_old_price_is_null(): void
    {
        Product::factory()->create(['slug' => 'no-sale', 'old_price' => null]);

        $response = $this->getJson('/api/products/no-sale');

        $this->assertNull($response->json('product.discount_percent'));
    }

    public function test_response_includes_uploaded_videos(): void
    {
        Product::factory()->create([
            'slug' => 'krem-z-video',
            'videos' => [['file' => 'videos/clip.mp4', 'poster' => 'images/videos/clip.png', 'title' => 'Як наносити']],
        ]);

        $videos = $this->getJson('/api/products/krem-z-video')->assertOk()->json('product.videos');

        $this->assertCount(1, $videos);
        $this->assertSame('videos/clip.mp4', $videos[0]['file']);
    }
}
