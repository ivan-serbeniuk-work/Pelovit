<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Завантаження ролика з редактора статті (кнопка media в TinyMCE).
 * Маршрут кладе файли в public — отже, він мусить бути закритий
 * адмінською авторизацією і тими самими правилами, що й поле форми.
 */
class EditorVideoUploadTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/admin/editor/upload-video';

    private function admin(): MoonshineUser
    {
        return MoonshineUser::firstOrCreate(
            ['email' => 'admin@test.local'],
            ['name' => 'Admin', 'password' => bcrypt('secret'), 'moonshine_user_role_id' => 1],
        );
    }

    public function test_guest_cannot_upload(): void
    {
        $disk = Storage::fake('public_root');

        $response = $this->post(self::URL, ['file' => UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4')]);

        $this->assertContains($response->status(), [302, 401, 403]);
        $this->assertEmpty($disk->allFiles('videos'));
    }

    public function test_admin_uploads_mp4_and_gets_location(): void
    {
        $disk = Storage::fake('public_root');

        $response = $this->actingAs($this->admin(), 'moonshine')
            ->postJson(self::URL, ['file' => UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4')])
            ->assertOk();

        $location = $response->json('location');

        $this->assertStringStartsWith('/videos/', (string) $location);
        $this->assertNotEmpty($disk->allFiles('videos'));
    }

    public function test_non_video_is_rejected(): void
    {
        $disk = Storage::fake('public_root');

        $this->actingAs($this->admin(), 'moonshine')
            ->postJson(self::URL, ['file' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])
            ->assertStatus(422);

        $this->assertEmpty($disk->allFiles('videos'));
    }

    public function test_oversized_video_is_rejected(): void
    {
        $disk = Storage::fake('public_root');

        $this->actingAs($this->admin(), 'moonshine')
            ->postJson(self::URL, ['file' => UploadedFile::fake()->create('huge.mp4', 51 * 1024 + 1, 'video/mp4')])
            ->assertStatus(422);

        $this->assertEmpty($disk->allFiles('videos'));
    }
}
