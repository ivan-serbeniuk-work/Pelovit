<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Картинки категорій мають лежати там, куди дивиться форма адмінки
 * (dir('images')). Рядки зі старим префіксом «categories/» давали в
 * адмінці шлях «images/categories/…» і виглядали як биті зображення.
 */
class NormalizeCategoryImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_moves_file_and_rewrites_path(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('categories/old.png', 'x');

        $category = Category::create([
            'name' => 'Догляд', 'slug' => 'dogliad', 'image' => 'categories/old.png',
        ]);

        $this->artisan('media:normalize-category-images')->assertSuccessful();

        $this->assertSame('images/old.png', $category->fresh()->image);
        $this->assertTrue($disk->exists('images/old.png'));
        $this->assertFalse($disk->exists('categories/old.png'));
    }

    public function test_leaves_correct_paths_alone(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('images/fine.png', 'x');

        $category = Category::create([
            'name' => 'Чисто', 'slug' => 'chysto', 'image' => 'images/fine.png',
        ]);

        $this->artisan('media:normalize-category-images')
            ->expectsOutputToContain('Усі шляхи вже в порядку.')
            ->assertSuccessful();

        $this->assertSame('images/fine.png', $category->fresh()->image);
    }

    /**
     * Повторний запуск нічого не ламає — команда має бути ідемпотентною.
     */
    public function test_is_idempotent(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('categories/old.png', 'x');

        $category = Category::create([
            'name' => 'Догляд', 'slug' => 'dogliad', 'image' => 'categories/old.png',
        ]);

        $this->artisan('media:normalize-category-images')->assertSuccessful();
        $this->artisan('media:normalize-category-images')->assertSuccessful();

        $this->assertSame('images/old.png', $category->fresh()->image);
        $this->assertTrue($disk->exists('images/old.png'));
    }

    public function test_dry_run_changes_nothing(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('categories/old.png', 'x');

        $category = Category::create([
            'name' => 'Догляд', 'slug' => 'dogliad', 'image' => 'categories/old.png',
        ]);

        $this->artisan('media:normalize-category-images', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('categories/old.png', $category->fresh()->image);
        $this->assertTrue($disk->exists('categories/old.png'));
    }
}
