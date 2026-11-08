<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\MoonShine\Resources\Post\PostResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Завантаження відео через адмінку — дзеркало ImageUploadTest.
 *
 * Перевіряємо те саме, що й для картинок: файл доїжджає на диск
 * public_root у теку свого поля, а завеликий чи чужий формат дає
 * зрозумілу помилку валідації, а не 500 і не тишу.
 */
class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): MoonshineUser
    {
        // firstOrCreate, бо деякі тести сабмітять форму двічі.
        return MoonshineUser::firstOrCreate(
            ['email' => 'admin@test.local'],
            ['name' => 'Admin', 'password' => bcrypt('secret'), 'moonshine_user_role_id' => 1],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function submit(string $resourceUri, int $id, array $payload): TestResponse
    {
        return $this->actingAs($this->admin(), 'moonshine')
            ->put("/admin/resource/$resourceUri/crud/$id", $payload);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Догляд', 'slug' => 'dogliad']);

        return Product::create([
            'name' => 'Крем', 'slug' => 'krem', 'price' => 100, 'stock' => 1,
            'is_active' => true, 'category_id' => $category->getKey(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => 100,
            'stock' => 1,
            'category_id' => $product->category_id,
        ];
    }

    private function fakeVideo(string $name = 'clip.mp4', int $kilobytes = 2048): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'video/mp4');
    }

    public function test_product_accepts_mp4(): void
    {
        $disk = Storage::fake('public_root');
        $product = $this->product();

        $this->submit('product-resource', $product->getKey(), [
            ...$this->productPayload($product),
            'videos' => [['file' => $this->fakeVideo()]],
        ])->assertStatus(302)->assertSessionHasNoErrors();

        $videos = $product->fresh()->videos;

        $this->assertIsArray($videos);
        $this->assertCount(1, $videos);
        $this->assertStringStartsWith('videos/', $videos[0]['file']);
        $this->assertNotEmpty($disk->allFiles('videos'));
    }

    /**
     * YouTube-посилання і залиті файли живуть поряд: одне не затирає інше.
     */
    public function test_product_keeps_youtube_url_alongside_files(): void
    {
        Storage::fake('public_root');
        $product = $this->product();

        $this->submit('product-resource', $product->getKey(), [
            ...$this->productPayload($product),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'videos' => [['file' => $this->fakeVideo()]],
        ])->assertStatus(302)->assertSessionHasNoErrors();

        $fresh = $product->fresh();

        $this->assertSame('dQw4w9WgXcQ', $fresh->youtube_id);
        $this->assertCount(1, $fresh->videos);
    }

    public function test_post_accepts_mp4(): void
    {
        $disk = Storage::fake('public_root');
        $post = Post::create(['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст']);

        $this->submit('post-resource', $post->getKey(), [
            'title' => 'Стаття',
            'slug' => 'stattya',
            'body' => 'Текст',
            'video_file' => $this->fakeVideo(),
        ])->assertStatus(302)->assertSessionHasNoErrors();

        $this->assertStringStartsWith('videos/', (string) $post->fresh()->video_file);
        $this->assertNotEmpty($disk->allFiles('videos'));
    }

    /**
     * Шлях у формі не має подвоюватись: MoonShine приклеює dir до значення,
     * і 'videos/' + 'videos/x.mp4' колись уже давало шлях у нікуди.
     */
    public function test_post_video_url_matches_stored_path(): void
    {
        $post = Post::create([
            'title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст',
            'video_file' => 'videos/clip.mp4',
        ]);

        $this->actingAs($this->admin(), 'moonshine')
            ->get(app(PostResource::class)->getFormPageUrl($post->id))
            ->assertOk()
            ->assertSee('/videos/clip.mp4', false)
            ->assertDontSee('/videos/videos/clip.mp4', false);
    }

    public function test_oversized_video_is_rejected(): void
    {
        $disk = Storage::fake('public_root');
        $post = Post::create(['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст']);

        $this->submit('post-resource', $post->getKey(), [
            'title' => 'Стаття',
            'slug' => 'stattya',
            'body' => 'Текст',
            'video_file' => $this->fakeVideo('huge.mp4', 51 * 1024 + 1),
        ])->assertSessionHasErrors('video_file', null, 'post-resource');

        $this->assertEmpty($disk->allFiles('videos'));
        $this->assertNull($post->fresh()->video_file);
    }

    /**
     * .mov не беремо свідомо: айфон пише HEVC у quicktime, якого браузери
     * не грають, а транскодувати на проді нема чим.
     */
    public function test_quicktime_and_image_are_rejected(): void
    {
        Storage::fake('public_root');
        $post = Post::create(['title' => 'Стаття', 'slug' => 'stattya', 'body' => 'Текст']);

        $this->submit('post-resource', $post->getKey(), [
            'title' => 'Стаття',
            'slug' => 'stattya',
            'body' => 'Текст',
            'video_file' => UploadedFile::fake()->create('clip.mov', 2048, 'video/quicktime'),
        ])->assertSessionHasErrors('video_file', null, 'post-resource');

        $this->submit('post-resource', $post->getKey(), [
            'title' => 'Стаття',
            'slug' => 'stattya',
            'body' => 'Текст',
            'video_file' => UploadedFile::fake()->image('photo.png', 400, 300),
        ])->assertSessionHasErrors('video_file', null, 'post-resource');
    }
}
