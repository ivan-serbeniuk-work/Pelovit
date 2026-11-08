<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Підказки під полем пошуку: кілька рядків у випадайці, не каталог.
 */
class SearchSuggestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_products_and_categories(): void
    {
        Category::create(['name' => 'Креми для обличчя', 'slug' => 'kremy', 'is_active' => true]);
        Product::factory()->create(['name' => 'Крем нічний']);
        Product::factory()->create(['name' => 'Маска глиняна']);

        $response = $this->getJson('/api/search/suggest?q=крем')->assertOk();

        $this->assertCount(1, $response->json('products'));
        $this->assertSame('Крем нічний', $response->json('products.0.name'));
        $this->assertCount(1, $response->json('categories'));
        $this->assertSame('kremy', $response->json('categories.0.slug'));
        $this->assertSame(1, $response->json('total'));
    }

    /** У випадайці лише те, що треба намалювати рядок. */
    public function test_product_payload_is_small(): void
    {
        Product::factory()->create(['name' => 'Крем нічний']);

        $product = $this->getJson('/api/search/suggest?q=крем')->assertOk()->json('products.0');

        $this->assertSame(
            ['id', 'name', 'slug', 'image', 'price', 'old_price', 'discount_percent'],
            array_keys($product),
        );
    }

    public function test_limits_products_but_counts_all(): void
    {
        Product::factory(10)->create(['name' => 'Крем']);

        $response = $this->getJson('/api/search/suggest?q=крем')->assertOk();

        $this->assertCount(6, $response->json('products'));
        $this->assertSame(10, $response->json('total'));
    }

    public function test_short_query_returns_empty_lists(): void
    {
        Product::factory()->create(['name' => 'Крем нічний']);

        $response = $this->getJson('/api/search/suggest?q=к')->assertOk();

        $this->assertSame([], $response->json('products'));
        $this->assertSame([], $response->json('categories'));
        $this->assertSame(0, $response->json('total'));
    }

    public function test_hidden_products_and_categories_are_skipped(): void
    {
        Product::factory()->inactive()->create(['name' => 'Крем прихований']);
        Category::create(['name' => 'Креми', 'slug' => 'kremy', 'is_active' => false]);

        $response = $this->getJson('/api/search/suggest?q=крем')->assertOk();

        $this->assertSame([], $response->json('products'));
        $this->assertSame([], $response->json('categories'));
    }

    /** Точний збіг назви має стояти першим. */
    public function test_exact_name_match_comes_first(): void
    {
        Product::factory()->create(['name' => 'Крем нічний відновлювальний']);
        Product::factory()->create(['name' => 'Крем']);

        $names = collect($this->getJson('/api/search/suggest?q=крем')->json('products'))
            ->pluck('name')->all();

        $this->assertSame(['Крем', 'Крем нічний відновлювальний'], $names);
    }
}
