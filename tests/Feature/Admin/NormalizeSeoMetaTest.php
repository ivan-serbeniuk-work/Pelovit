<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Команда seo:normalize-meta лагодить мету, залиту при переїзді з WordPress.
 * Головне, що тут закріплено: вона не чіпає тексти, написані руками.
 */
class NormalizeSeoMetaTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Косметика', 'slug' => 'kosmetika']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Остео-гель 120мл',
            'price' => 100,
            'description' => '<h3>Гель для м&#8217;язів</h3><ul>'
                .'<li>при відчутті втоми у м&#8217;язах і суглобах;</li>'
                .'<li>після фізичних і спортивних навантажень;</li>'
                .'<li>у складі масажних і відновлювальних процедур.</li></ul>',
            ...$attributes,
        ]);
    }

    public function test_title_gets_brand_and_fits_the_limit(): void
    {
        $product = $this->product(['meta_title' => 'Остео-гель 120мл']);

        $this->artisan('seo:normalize-meta --force')->assertSuccessful();

        $title = $product->fresh()->meta_title;

        $this->assertSame('Остео-гель 120мл — купити в Україні | PELOVIT', $title);
        $this->assertLessThanOrEqual(60, mb_strlen($title));
    }

    public function test_long_name_keeps_title_within_the_limit(): void
    {
        $product = $this->product([
            'name' => 'Крем для обличчя з комбучею 35+ KOMBUCHA FACE CREAM 50 мл',
            'meta_title' => '',
        ]);

        $this->artisan('seo:normalize-meta --force')->assertSuccessful();

        $this->assertLessThanOrEqual(60, mb_strlen((string) $product->fresh()->meta_title));
    }

    public function test_description_is_rebuilt_without_entities_and_within_the_limit(): void
    {
        $product = $this->product([
            'meta_description' => 'при відчутті втоми у м&#8217;язах і суглобах; після фізичних',
        ]);

        $this->artisan('seo:normalize-meta --force')->assertSuccessful();

        $description = (string) $product->fresh()->meta_description;

        $this->assertStringNotContainsString('&#', $description);
        $this->assertStringContainsString('м’язів', $description);
        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertTrue(mb_check_encoding($description, 'UTF-8'));
        // Пункти списку не злипаються в одне слово.
        $this->assertStringNotContainsString('суглобахпісля', $description);
    }

    public function test_handwritten_meta_is_left_alone(): void
    {
        $handwritten = 'Остео-гель PELOVIT з мінералами Мертвого моря — знімає втому '
            .'у м’язах і суглобах. Замовляйте з доставкою по Україні.';

        $product = $this->product([
            'meta_title' => 'Остео-гель 120мл — гель для м’язів і суглобів | PELOVIT',
            'meta_description' => $handwritten,
        ]);

        $this->artisan('seo:normalize-meta --force')->assertSuccessful();

        $fresh = $product->fresh();

        $this->assertSame('Остео-гель 120мл — гель для м’язів і суглобів | PELOVIT', $fresh->meta_title);
        $this->assertSame($handwritten, $fresh->meta_description);
    }

    public function test_second_run_changes_nothing(): void
    {
        $product = $this->product(['meta_title' => 'Остео-гель 120мл']);

        $this->artisan('seo:normalize-meta --force')->assertSuccessful();
        $after = $product->fresh()->only(['meta_title', 'meta_description']);

        $this->artisan('seo:normalize-meta')
            ->expectsOutputToContain('Мета вже в порядку')
            ->assertSuccessful();

        $this->assertSame($after, $product->fresh()->only(['meta_title', 'meta_description']));
    }

    public function test_dry_run_writes_nothing(): void
    {
        $product = $this->product(['meta_title' => 'Остео-гель 120мл']);

        $this->artisan('seo:normalize-meta --dry-run')->assertSuccessful();

        $this->assertSame('Остео-гель 120мл', $product->fresh()->meta_title);
    }

    public function test_post_meta_is_filled_without_step_numbers(): void
    {
        $post = Post::create([
            'title' => 'Мінеральна біостимулююча ванна, тип 1',
            'slug' => 'mineralna-vanna-typ-1',
            'excerpt' => '1. Додати у ванну 0,5 л препарату. 2. Прийняти ванну 15 хвилин. 3. Обполоснутися.',
            'body' => '<p>Текст статті.</p>',
            'is_published' => true,
        ]);

        $this->artisan('seo:normalize-meta --only=posts --force')->assertSuccessful();

        $fresh = $post->fresh();

        $this->assertSame('Мінеральна біостимулююча ванна, тип 1 | PELOVIT', $fresh->meta_title);
        $this->assertStringStartsWith('Додати у ванну', (string) $fresh->meta_description);
        $this->assertStringNotContainsString('2.', (string) $fresh->meta_description);
    }

    public function test_only_option_rejects_unknown_value(): void
    {
        $this->artisan('seo:normalize-meta --only=banners')->assertFailed();
    }
}
