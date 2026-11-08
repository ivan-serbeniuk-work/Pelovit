<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Перенос підкатегорій і видалення старого батька.
 *
 * Схема тут недружня: products.category_id — cascadeOnDelete (видалення
 * категорії стирає товари), categories.parent_id — nullOnDelete (діти
 * стають кореневими). Тести фіксують, що команда робить це безпечно.
 */
class MoveCategoryChildrenTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name, string $slug, ?int $parent = null): Category
    {
        return Category::create(['name' => $name, 'slug' => $slug, 'parent_id' => $parent, 'is_active' => true]);
    }

    public function test_moves_children_and_deletes_source(): void
    {
        $from = $this->category('Лікувальні', 'likuvalni');
        $to = $this->category('Мед вироби', 'med');
        $child = $this->category('ШКТ', 'shkt', $from->id);
        $product = Product::factory()->create(['category_id' => $child->id]);

        $this->artisan('categories:move-children likuvalni med --delete-source')->assertSuccessful();

        $this->assertSame($to->id, $child->fresh()->parent_id);
        $this->assertNull(Category::find($from->id));
        $this->assertNotNull($product->fresh(), 'Товар у підкатегорії має лишитися');
    }

    /** Товари у вихідній категорії — видалення скасовується. */
    public function test_refuses_to_delete_category_with_products(): void
    {
        $from = $this->category('Лікувальні', 'likuvalni');
        $to = $this->category('Мед вироби', 'med');
        $child = $this->category('ШКТ', 'shkt', $from->id);
        $product = Product::factory()->create(['category_id' => $from->id]);

        $this->artisan('categories:move-children likuvalni med --delete-source')->assertFailed();

        $this->assertNotNull(Category::find($from->id));
        $this->assertNotNull($product->fresh());
        $this->assertSame($from->id, $child->fresh()->parent_id, 'Нічого не переносимо, якщо видалення неможливе');
    }

    public function test_dry_run_changes_nothing(): void
    {
        $from = $this->category('Лікувальні', 'likuvalni');
        $this->category('Мед вироби', 'med');
        $child = $this->category('ШКТ', 'shkt', $from->id);

        $this->artisan('categories:move-children likuvalni med --delete-source --dry-run')->assertSuccessful();

        $this->assertSame($from->id, $child->fresh()->parent_id);
        $this->assertNotNull(Category::find($from->id));
    }

    public function test_can_move_without_deleting_source(): void
    {
        $from = $this->category('Лікувальні', 'likuvalni');
        $to = $this->category('Мед вироби', 'med');
        $child = $this->category('ШКТ', 'shkt', $from->id);

        $this->artisan('categories:move-children likuvalni med')->assertSuccessful();

        $this->assertSame($to->id, $child->fresh()->parent_id);
        $this->assertNotNull(Category::find($from->id), 'Без --delete-source категорія лишається');
    }

    public function test_unknown_category_fails(): void
    {
        $this->category('Мед вироби', 'med');

        $this->artisan('categories:move-children nema med')->assertFailed();
    }
}
