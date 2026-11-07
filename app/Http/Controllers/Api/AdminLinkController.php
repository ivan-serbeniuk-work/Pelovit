<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\TeamMember\TeamMemberResource;
use App\MoonShine\Resources\Testimonial\TestimonialResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Віддає вітрині посилання на редагування того, з чого складена сторінка —
 * але тільки якщо в браузері є активна сесія MoonShine. Маршрут підключений
 * до групи `web`, бо працює з сесійною кукою.
 *
 * Вітрина передає список цілей через ?targets=, кожна у вигляді:
 *   `product:18` — форма конкретного запису;
 *   `faq`        — сторінка-список розділу.
 * Сторінка може просити кілька цілей: головна збирається з банерів,
 * FAQ і відгуків, і адміну потрібні всі три, а не одна.
 */
class AdminLinkController extends Controller
{
    /**
     * Дозволені цілі: ключ → [клас ресурсу, підпис форми, підпис розділу].
     * Тільки контент сторінок. Замовлення, заявки, підписники й користувачі
     * не є частиною вітрини, тож звідси їх не відкриваємо.
     *
     * @var array<string, array{0: class-string, 1: ?string, 2: string}>
     */
    private const TARGETS = [
        'product' => [ProductResource::class, 'Товар', 'Товари'],
        'post' => [PostResource::class, 'Стаття', 'Статті'],
        'category' => [CategoryResource::class, 'Категорія', 'Категорії'],
        'banner' => [BannerResource::class, 'Банер', 'Банери'],
        'faq' => [FaqResource::class, 'Питання', 'FAQ'],
        'testimonial' => [TestimonialResource::class, 'Відгук', 'Відгуки'],
        'brand-case' => [BrandCaseResource::class, 'Кейс', 'Кейси брендів'],
        'team' => [TeamMemberResource::class, 'Співробітник', 'Команда'],
        'calculator-option' => [CalculatorOptionResource::class, 'Опція', 'Опції калькулятора'],
        'calculator-tier' => [CalculatorTierResource::class, 'Тираж', 'Тиражі калькулятора'],
        'about-stat' => [AboutStatResource::class, 'Показник', 'Показники «Про нас»'],
        'about-value' => [AboutValueResource::class, 'Цінність', 'Цінності «Про нас»'],
        'about-milestone' => [AboutMilestoneResource::class, 'Віха', 'Історія «Про нас»'],
    ];

    /** Сторінки адмінки без ресурсу (у них немає окремих записів). */
    private const PAGES = [
        'settings' => [SettingsPage::class, 'Налаштування'],
    ];

    /** Більше цілей на одну сторінку не буває — захист від роздутого запиту. */
    private const MAX_TARGETS = 8;

    public function show(Request $request): JsonResponse
    {
        if (! Auth::guard('moonshine')->check()) {
            return response()->json(['authenticated' => false, 'links' => []]);
        }

        $requested = array_slice(
            array_filter(array_map('trim', explode(',', (string) $request->query('targets')))),
            0,
            self::MAX_TARGETS
        );

        $links = [];

        foreach ($requested as $target) {
            $link = $this->resolve($target);

            if ($link !== null) {
                $links[] = $link;
            }
        }

        return response()->json(['authenticated' => true, 'links' => $links]);
    }

    /** @return array{label: string, url: string}|null */
    private function resolve(string $target): ?array
    {
        [$name, $key] = array_pad(explode(':', $target, 2), 2, null);

        if (isset(self::PAGES[$name])) {
            [$page, $label] = self::PAGES[$name];

            return ['label' => $label, 'url' => app($page)->getUrl()];
        }

        if (! isset(self::TARGETS[$name])) {
            return null;
        }

        [$class, $formLabel, $indexLabel] = self::TARGETS[$name];
        $resource = app($class);

        // Без ключа — список розділу: саме це потрібно сторінкам-каталогам
        // і головній, де окремого запису під сторінкою немає.
        if ($key === null || $key === '') {
            return ['label' => $indexLabel, 'url' => $resource->getIndexPageUrl()];
        }

        $id = filter_var($key, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            return null;
        }

        return ['label' => $formLabel, 'url' => $resource->getFormPageUrl($id)];
    }
}
