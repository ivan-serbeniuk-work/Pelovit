<?php

namespace Tests\Feature\Admin;

use App\Models\AboutSection;
use App\Models\Category;
use App\MoonShine\Resources\AboutSection\AboutSectionResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Клік по рядку таблиці відкриває форму редагування (BaseIndexPage).
 */
class IndexPageClickActionTest extends TestCase
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

    public function test_index_table_rows_are_clickable(): void
    {
        AboutSection::create(['title' => 'Дослідження', 'sort_order' => 1, 'is_active' => true]);

        $admin = $this->admin();

        $this->actingAs($admin, 'moonshine')
            ->get(app(AboutSectionResource::class)->getIndexPageUrl())
            ->assertOk();

        $this->actingAs($admin, 'moonshine')
            ->get('/admin/component/about-section-index-page/about-section-resource?_component_name=index-table-about-section-resource')
            ->assertOk()
            ->assertSee('data-click-action="edit"', false)
            ->assertSee('rowClickAction', false);
    }

    /**
     * Category перевизначає modifyListComponent (reorderable) і не кличе parent —
     * перевіряємо, що clickAction там усе одно на місці.
     */
    public function test_click_action_survives_custom_modify_list_component(): void
    {
        Category::create(['name' => 'Догляд', 'slug' => 'dogliad']);

        $this->actingAs($this->admin(), 'moonshine')
            ->get('/admin/component/category-index-page/category-resource?_component_name=index-table-category-resource')
            ->assertOk()
            ->assertSee('data-click-action="edit"', false);
    }
}
