<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_published_posts(): void
    {
        Post::factory(5)->create();
        Post::factory(2)->unpublished()->create();

        $response = $this->getJson('/api/journal');

        $response->assertOk()->assertJsonStructure(['posts', 'featured', 'categories']);
        $this->assertEquals(5, $response->json('posts.total'));
    }

    public function test_index_posts_ordered_newest_first(): void
    {
        Post::factory()->create(['title' => 'Old', 'published_at' => now()->subDays(10)]);
        Post::factory()->create(['title' => 'New', 'published_at' => now()->subDay()]);

        $response = $this->getJson('/api/journal');

        $this->assertEquals('New', $response->json('posts.data.0.title'));
    }

    public function test_show_returns_post_by_slug(): void
    {
        Post::factory()->create(['slug' => 'mii-post', 'title' => 'Мій пост']);

        $this->getJson('/api/journal/mii-post')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Мій пост']);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/journal/ne-isnuie')
            ->assertNotFound();
    }

    public function test_show_returns_404_for_unpublished_post(): void
    {
        Post::factory()->unpublished()->create(['slug' => 'draft']);

        $this->getJson('/api/journal/draft')
            ->assertNotFound();
    }

    public function test_show_includes_related_posts(): void
    {
        Post::factory()->create(['slug' => 'main-post', 'category' => 'health']);
        Post::factory(3)->create(['category' => 'health']);
        Post::factory(2)->create(['category' => 'beauty']);

        $response = $this->getJson('/api/journal/main-post');

        $response->assertOk()->assertJsonStructure(['post', 'related']);
    }

    public function test_formatted_date_is_appended(): void
    {
        Post::factory()->create(['slug' => 'date-test', 'published_at' => '2026-01-15']);

        $response = $this->getJson('/api/journal/date-test');

        $this->assertArrayHasKey('formatted_date', $response->json('post'));
        $this->assertNotEmpty($response->json('post.formatted_date'));
    }

    /**
     * Відео приїжджає на сторінку статті, але не в картки списку —
     * CARD_FIELDS лишається вузьким навмисно.
     */
    public function test_show_returns_video_fields(): void
    {
        Post::factory()->create([
            'slug' => 'post-z-video',
            'video_file' => 'videos/clip.mp4',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $response = $this->getJson('/api/journal/post-z-video')->assertOk();

        $this->assertSame('videos/clip.mp4', $response->json('post.video_file'));
        $this->assertSame('dQw4w9WgXcQ', $response->json('post.youtube_id'));
    }

    public function test_index_cards_do_not_expose_video(): void
    {
        Post::factory()->create(['video_file' => 'videos/clip.mp4']);

        $card = $this->getJson('/api/journal')->assertOk()->json('posts.data.0');

        $this->assertArrayNotHasKey('video_file', $card);
        $this->assertArrayNotHasKey('video_url', $card);
    }
}
