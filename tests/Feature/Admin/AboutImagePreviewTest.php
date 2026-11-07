<?php

namespace Tests\Feature\Admin;

use App\Models\AboutMilestone;
use App\MoonShine\Resources\AboutMilestone\AboutMilestoneResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Прев'ю фото в адмінці має вести на той самий файл, що й вітрина.
 * Було dir('about') при значеннях виду «images/foo.jpg» — MoonShine
 * додавав свою теку і просив /about/images/foo.jpg, тобто 404.
 */
class AboutImagePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_preview_points_to_existing_file(): void
    {
        $milestone = AboutMilestone::create([
            'year' => '2010',
            'text' => 'Заснування бренду',
            'image' => 'images/handwash3.jpg',
            'placement' => 'top',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $admin = MoonshineUser::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('secret'),
            'moonshine_user_role_id' => 1,
        ]);

        $this->actingAs($admin, 'moonshine')
            ->get(app(AboutMilestoneResource::class)->getFormPageUrl($milestone->getKey()))
            ->assertOk()
            ->assertSee('/images/handwash3.jpg', false)
            ->assertDontSee('/about/images/', false);
    }
}
