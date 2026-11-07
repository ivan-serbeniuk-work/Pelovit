<?php

namespace Tests\Feature\Api;

use App\Models\AboutMilestone;
use App\Models\AboutStat;
use App\Models\AboutValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_structure_when_nothing_is_filled(): void
    {
        $this->getJson('/api/about')
            ->assertOk()
            ->assertJson(['stats' => [], 'values' => [], 'milestones' => ['top' => [], 'bottom' => []]]);
    }

    public function test_returns_active_content_in_sort_order(): void
    {
        AboutStat::create(['value' => '88', 'label' => 'Другий', 'sort_order' => 2]);
        AboutStat::create(['value' => '50', 'label' => 'Перший', 'sort_order' => 1]);

        $labels = array_column($this->getJson('/api/about')->json('stats'), 'label');

        $this->assertSame(['Перший', 'Другий'], $labels);
    }

    public function test_inactive_records_are_hidden(): void
    {
        AboutValue::create(['title' => 'Видима', 'text' => 'т']);
        AboutValue::create(['title' => 'Прихована', 'text' => 'т', 'is_active' => false]);

        $titles = array_column($this->getJson('/api/about')->json('values'), 'title');

        $this->assertSame(['Видима'], $titles);
    }

    /** Віхи приходять уже розкладені двома рядами, як у макеті. */
    public function test_milestones_are_split_by_placement(): void
    {
        AboutMilestone::create(['year' => '2010', 'text' => 'т', 'placement' => 'top']);
        AboutMilestone::create(['year' => '2013', 'text' => 'т', 'placement' => 'bottom']);

        $json = $this->getJson('/api/about')->json('milestones');

        $this->assertSame(['2010'], array_column($json['top'], 'year'));
        $this->assertSame(['2013'], array_column($json['bottom'], 'year'));
    }
}
