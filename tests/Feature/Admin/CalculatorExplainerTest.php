<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CalculatorOption;
use App\Models\CalculatorTier;
use App\Models\Setting;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use App\Support\CalculatorExplainer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MoonShine\Laravel\Models\MoonshineUser;
use Tests\TestCase;

/**
 * Пояснення в адмінці дублює формулу з frontend/app/pages/
 * kontractne-vyrobnyctvo.vue. Тест закріплює числа, щоб розходження
 * з вітриною не поїхало в адмінку тихо.
 */
class CalculatorExplainerTest extends TestCase
{
    use RefreshDatabase;

    private function seedCalculator(): void
    {
        // Реальні значення з прода — ними ж порахований приклад нижче.
        $options = [
            ['product', 'Крем', 1.2, 1],
            ['product', 'Сироватка', 2.1, 2],
            ['formula', 'Базова формула', 1, 1],
            ['formula', 'Формула з активами', 1.35, 2],
            ['packaging', 'Пляшка з переробленого ПЕТ', 12, 1],
            ['label', 'Паперова', 2.5, 1],
            ['box', 'Так', 9, 1],
        ];

        foreach ($options as [$group, $name, $value, $sort]) {
            CalculatorOption::create([
                'group' => $group, 'name' => $name, 'value' => $value,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        foreach ([[100, 0], [300, 5], [500, 10], [1000, 15]] as [$qty, $discount]) {
            CalculatorTier::create(['min_quantity' => $qty, 'discount_percent' => $discount]);
        }

        Setting::set('calc_min_batch_total', 7500);
        Setting::set('calc_spread_percent', 12);
        Setting::set('calc_production_days', 15);
    }

    public function test_unit_price_matches_the_storefront_formula(): void
    {
        $this->seedCalculator();

        // 1.2 × 30 мл × 1 = 36 рецептури + 12 + 2.5 + 9 = 23.5 матеріалів.
        $this->assertSame(59.5, app(CalculatorExplainer::class)->baseUnitPrice());
    }

    public function test_highest_passed_tier_wins(): void
    {
        $this->seedCalculator();
        $explainer = app(CalculatorExplainer::class);

        $this->assertSame(0.0, $explainer->discountFor(100));
        $this->assertSame(5.0, $explainer->discountFor(400), 'На 400 шт має діяти ступінь від 300.');
        $this->assertSame(15.0, $explainer->discountFor(5000));
    }

    public function test_batch_total_drops_with_quantity_discount(): void
    {
        $this->seedCalculator();
        $explainer = app(CalculatorExplainer::class);

        $this->assertSame('5 950', $explainer->money($explainer->batchTotalFor(100)));
        $this->assertSame('26 775', $explainer->money($explainer->batchTotalFor(500)));
    }

    public function test_required_quantity_accounts_for_the_discount_it_unlocks(): void
    {
        $this->seedCalculator();

        // 7500 / 59.5 = 127 шт, і на 127 шт знижки ще немає — число стабільне.
        $this->assertSame(127, app(CalculatorExplainer::class)->requiredQuantity());
    }

    public function test_required_quantity_grows_past_a_discount_threshold(): void
    {
        $this->seedCalculator();
        // Дешевий продукт: потрібний тираж перескакує поріг 300 шт, де
        // одиниця дешевіє на 5% — просте ділення дало б замало.
        CalculatorOption::where('group', 'product')->delete();
        CalculatorOption::create([
            'group' => 'product', 'name' => 'Гель', 'value' => 0.02,
            'sort_order' => 1, 'is_active' => true,
        ]);

        $explainer = app(CalculatorExplainer::class);
        $required = $explainer->requiredQuantity();

        $this->assertGreaterThan(300, $required);
        $this->assertGreaterThanOrEqual(
            7500,
            $explainer->batchTotalFor($required),
            'Знайдений тираж мусить дотягувати до мінімуму вже зі знижкою.'
        );
    }

    public function test_range_shows_the_spread(): void
    {
        $this->seedCalculator();

        $this->assertSame('5 236–6 664 ₴', app(CalculatorExplainer::class)->range(5950));
    }

    public function test_without_product_or_formula_there_is_no_price(): void
    {
        $this->seedCalculator();
        CalculatorOption::where('group', 'formula')->update(['is_active' => false]);

        $explainer = app(CalculatorExplainer::class);

        $this->assertFalse($explainer->hasPricing());
        $this->assertSame(0.0, $explainer->baseUnitPrice());
        $this->assertStringContainsString('не рахує', $explainer->render());
    }

    public function test_admin_pages_show_the_explainer(): void
    {
        $this->seedCalculator();

        $admin = MoonshineUser::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('secret'),
            'moonshine_user_role_id' => 1,
        ]);

        foreach ([CalculatorOptionResource::class, CalculatorTierResource::class] as $resource) {
            $this->actingAs($admin, 'moonshine')
                ->get(app($resource)->getIndexPageUrl())
                ->assertOk()
                ->assertSee('Як працює калькулятор')
                ->assertSee('ціна за 1 шт', false)
                ->assertSee('5 950', false);
        }
    }
}
