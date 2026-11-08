<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Видалення ролика з адмінки.
 *
 * Хрестик біля файлу прибирає лише прихований input у формі, тож сервер
 * бачить рядок без file. Для звичайного поля File MoonShine сам чистить
 * диск, а для File всередині Json — ні: у базі лишався порожній рядок,
 * а 50-мегабайтний ролик висів у public/videos назавжди. Це закриває
 * трейт CleansUpVideoFiles, і саме його тут перевіряємо.
 */
class RemoveVideoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): MoonshineUser
    {
        return MoonshineUser::firstOrCreate(
            ['email' => 'admin@test.local'],
            ['name' => 'Admin', 'password' => bcrypt('secret'), 'moonshine_user_role_id' => 1],
        );
    }

    private function product(array $videos): Product
    {
        $category = Category::create(['name' => 'Догляд', 'slug' => 'dogliad']);

        return Product::create([
            'name' => 'Крем', 'slug' => 'krem', 'price' => 100, 'stock' => 1,
            'is_active' => true, 'category_id' => $category->getKey(),
            'videos' => $videos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function saveProduct(Product $product, array $extra = []): void
    {
        $this->actingAs($this->admin(), 'moonshine')->put(
            "/admin/resource/product-resource/crud/{$product->getKey()}",
            [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => 100,
                'stock' => 1,
                'category_id' => $product->category_id,
                ...$extra,
            ],
        );
    }

    public function test_removing_video_clears_row_and_file(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('videos/old.mp4', 'x');
        $disk->put('images/videos/poster.png', 'x');

        $product = $this->product([
            ['file' => 'videos/old.mp4', 'poster' => 'images/videos/poster.png', 'title' => 'Як наносити'],
        ]);

        // Саме такий payload шле форма після кліку по хрестику: рядок є,
        // прихованого значення файлу вже немає.
        $this->saveProduct($product, ['videos' => [['title' => 'Як наносити']]]);

        $this->assertNull($product->fresh()->videos, 'Порожній рядок має зникнути');
        $this->assertFalse($disk->exists('videos/old.mp4'), 'Ролик має піти з диска');
        $this->assertFalse($disk->exists('images/videos/poster.png'), 'Постер теж');
    }

    public function test_removing_one_of_two_videos_keeps_the_other(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('videos/one.mp4', 'x');
        $disk->put('videos/two.mp4', 'x');

        $product = $this->product([
            ['file' => 'videos/one.mp4', 'title' => 'Перше'],
            ['file' => 'videos/two.mp4', 'title' => 'Друге'],
        ]);

        $this->saveProduct($product, ['videos' => [['hidden_file' => 'videos/two.mp4', 'title' => 'Друге']]]);

        $videos = $product->fresh()->videos;

        $this->assertCount(1, $videos);
        $this->assertSame('videos/two.mp4', $videos[0]['file']);
        $this->assertFalse($disk->exists('videos/one.mp4'), 'Знятий ролик має піти з диска');
        $this->assertTrue($disk->exists('videos/two.mp4'), 'Той, що лишився, чіпати не можна');
    }

    /**
     * У статті поле звичайне, не json — там чистку робить сам MoonShine.
     * Тест закріплює цю поведінку, щоб не з'ясовувати її вдруге.
     */
    public function test_removing_post_video_clears_column_and_file(): void
    {
        $disk = Storage::fake('public_root');
        $disk->put('videos/post.mp4', 'x');

        $post = Post::create([
            'title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст',
            'video_file' => 'videos/post.mp4',
        ]);

        $this->actingAs($this->admin(), 'moonshine')->put(
            "/admin/resource/post-resource/crud/{$post->getKey()}",
            ['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст'],
        );

        $this->assertNull($post->fresh()->video_file);
        $this->assertFalse($disk->exists('videos/post.mp4'));
    }
}
