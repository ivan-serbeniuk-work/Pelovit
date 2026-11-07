<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\SettingsPage;
use App\MoonShine\Resources\AboutMilestone\AboutMilestoneResource;
use App\MoonShine\Resources\AboutSection\AboutSectionResource;
use App\MoonShine\Resources\AboutStat\AboutStatResource;
use App\MoonShine\Resources\AboutValue\AboutValueResource;
use App\MoonShine\Resources\Banner\BannerResource;
use App\MoonShine\Resources\BrandCase\BrandCaseResource;
use App\MoonShine\Resources\CalculatorOption\CalculatorOptionResource;
use App\MoonShine\Resources\CalculatorTier\CalculatorTierResource;
use App\MoonShine\Resources\Category\CategoryResource;
use App\MoonShine\Resources\Faq\FaqResource;
use App\MoonShine\Resources\Lead\LeadResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use App\MoonShine\Resources\Order\OrderResource;
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\MoonShine\Resources\Subscriber\SubscriberResource;
use App\MoonShine\Resources\TeamMember\TeamMemberResource;
use App\MoonShine\Resources\Testimonial\TestimonialResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  CoreContract<MoonShineConfigurator>  $core
     */
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                OrderResource::class,
                ProductResource::class,
                CategoryResource::class,
                PostResource::class,
                BannerResource::class,
                SubscriberResource::class,
                TeamMemberResource::class,
                LeadResource::class,
                PromoCodeResource::class,
                CalculatorOptionResource::class,
                CalculatorTierResource::class,
                TestimonialResource::class,
                BrandCaseResource::class,
                FaqResource::class,
                AboutStatResource::class,
                AboutValueResource::class,
                AboutMilestoneResource::class,
                AboutSectionResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
                SettingsPage::class,
            ]);
    }
}
