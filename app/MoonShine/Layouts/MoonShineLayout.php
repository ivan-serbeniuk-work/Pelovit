<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\SettingsPage;
use App\MoonShine\Resources\AboutMilestone\AboutMilestoneResource;
use App\MoonShine\Resources\AboutStat\AboutStatResource;
use App\MoonShine\Resources\AboutValue\AboutValueResource;
use App\MoonShine\Resources\Banner\BannerResource;
use App\MoonShine\Resources\BrandCase\BrandCaseResource;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use App\MoonShine\Resources\Category\CategoryResource;
use App\MoonShine\Resources\Faq\FaqResource;
use App\MoonShine\Resources\Lead\LeadResource;
use App\MoonShine\Resources\Order\OrderResource;
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\MoonShine\Resources\Subscriber\SubscriberResource;
use App\MoonShine\Resources\TeamMember\TeamMemberResource;
use App\MoonShine\Resources\Testimonial\TestimonialResource;
use MoonShine\ColorManager\ColorManager;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

final class MoonShineLayout extends AppLayout
{
    /**
     * @var null|class-string<PaletteContract>
     */
    protected ?string $palette = PurplePalette::class;

    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            ...parent::menu(),
            MenuItem::make(OrderResource::class, 'Замовлення')->icon('shopping-bag'),
            MenuItem::make(LeadResource::class, 'Ліди')->icon('inbox'),
            MenuItem::make(PromoCodeResource::class, 'Промокоди')->icon('ticket'),
            MenuGroup::make('Каталог', [
                MenuItem::make(ProductResource::class, 'Товари')->icon('squares-2x2'),
                MenuItem::make(CategoryResource::class, 'Категорії')->icon('tag'),
            ])->icon('squares-plus'),
            MenuGroup::make('Контент', [
                MenuItem::make(BannerResource::class, 'Банери')->icon('photo'),
                MenuItem::make(PostResource::class, 'Журнал')->icon('document-text'),
                MenuItem::make(TeamMemberResource::class, 'Команда')->icon('user-group'),
                MenuItem::make(TestimonialResource::class, 'Відгуки')->icon('chat-bubble-left-right'),
                MenuItem::make(BrandCaseResource::class, 'Кейси брендів')->icon('sparkles'),
                MenuItem::make(FaqResource::class, 'FAQ')->icon('question-mark-circle'),
            ]),
            MenuGroup::make('Сторінка «Про нас»', [
                MenuItem::make(AboutStatResource::class, 'Показники')->icon('calculator'),
                MenuItem::make(AboutValueResource::class, 'Цінності')->icon('heart'),
                MenuItem::make(AboutMilestoneResource::class, 'Історія')->icon('clock'),
            ])->icon('pencil-square'),
            MenuGroup::make('Калькулятор', [
                MenuItem::make(CalculatorOptionResource::class, 'Опції')->icon('adjustments-horizontal'),
                MenuItem::make(CalculatorTierResource::class, 'Знижки за тираж')->icon('chart-bar'),
            ])->icon('calculator'),
            MenuItem::make(SubscriberResource::class, 'Підписники')->icon('users'),
            MenuItem::make(SettingsPage::class, 'Налаштування')->icon('cog-6-tooth'),
        ];
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }
}
