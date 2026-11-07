<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

class AdminLinkApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): MoonshineUser
    {
        return MoonshineUser::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('secret'),
            'moonshine_user_role_id' => 1,
        ]);
    }

    public function test_guest_gets_no_links(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/admin/edit-links?targets=product:{$product->id}")
            ->assertOk()
            ->assertJson(['authenticated' => false, 'links' => []]);
    }

    public function test_admin_gets_link_to_product_form(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson("/api/admin/edit-links?targets=product:{$product->id}")
            ->assertOk()
            ->assertJson(['authenticated' => true]);

        $this->assertCount(1, $response->json('links'));
        $this->assertSame('Товар', $response->json('links.0.label'));
        $this->assertStringContainsString('/admin/resource/product-resource', $response->json('links.0.url'));
        $this->assertStringEndsWith((string) $product->id, $response->json('links.0.url'));
    }

    public function test_admin_gets_link_to_post_form(): void
    {
        $post = Post::factory()->create();

        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson("/api/admin/edit-links?targets=post:{$post->id}")
            ->assertOk();

        $this->assertSame('Стаття', $response->json('links.0.label'));
        $this->assertStringContainsString('/admin/resource/post-resource', $response->json('links.0.url'));
    }

    /** Без ключа ціль означає розділ цілком — цього чекають сторінки-каталоги. */
    public function test_target_without_key_points_to_index_page(): void
    {
        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson('/api/admin/edit-links?targets=faq')
            ->assertOk();

        $this->assertSame('FAQ', $response->json('links.0.label'));
        $this->assertStringContainsString('faq-index-page', $response->json('links.0.url'));
    }

    public function test_admin_gets_several_links_in_declared_order(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson("/api/admin/edit-links?targets=product:{$product->id},banner,faq")
            ->assertOk();

        $this->assertSame(
            ['Товар', 'Банери', 'FAQ'],
            array_column($response->json('links'), 'label')
        );
    }

    /** Сторінка адмінки без ресурсу — у налаштувань немає окремих записів. */
    public function test_settings_target_points_to_page(): void
    {
        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson('/api/admin/edit-links?targets=settings')
            ->assertOk();

        $this->assertSame('Налаштування', $response->json('links.0.label'));
        $this->assertStringContainsString('settings-page', $response->json('links.0.url'));
    }

    public function test_unknown_and_malformed_targets_are_dropped(): void
    {
        $this->actingAs($this->admin(), 'moonshine')
            ->getJson('/api/admin/edit-links?targets=orders,subscriber,product:0,product:abc,,')
            ->assertOk()
            ->assertJson(['authenticated' => true, 'links' => []]);
    }

    public function test_targets_are_capped(): void
    {
        $targets = implode(',', array_fill(0, 20, 'faq'));

        $response = $this->actingAs($this->admin(), 'moonshine')
            ->getJson("/api/admin/edit-links?targets={$targets}")
            ->assertOk();

        $this->assertCount(8, $response->json('links'));
    }

    public function test_missing_targets_param_is_harmless(): void
    {
        $this->actingAs($this->admin(), 'moonshine')
            ->getJson('/api/admin/edit-links')
            ->assertOk()
            ->assertJson(['authenticated' => true, 'links' => []]);
    }
}
