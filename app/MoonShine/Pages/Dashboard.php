<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Order;
use Carbon\Carbon;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

#[SkipMenu]

class Dashboard extends Page
{
    public function getBreadcrumbs(): array
    {
        return ['#' => $this->getTitle()];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    protected function components(): iterable
    {
        $today = now()->toDateString();

        // Orders per day for last 7 days
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $counts = Order::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('cnt', 'date');
        $maxCount = max($counts->max() ?? 1, 1);

        $bars = $days->map(function ($date) use ($counts, $maxCount, $today) {
            $cnt = (int) $counts->get($date, 0);
            $pct = $maxCount > 0 ? round($cnt / $maxCount * 100) : 0;
            $day = Carbon::parse($date)->locale('uk')->isoFormat('dd');
            $dm = Carbon::parse($date)->format('d.m');
            $active = $date === $today;
            $barCls = $active ? 'ms-db-bar--today' : 'ms-db-bar--past';
            $lblCls = $active ? 'ms-db-lbl--today' : 'ms-db-lbl--past';
            $minH = $cnt > 0 ? '6px' : '0px';

            return "
            <div class='ms-db-col'>
                <span class='ms-db-cnt {$lblCls}'>".($cnt > 0 ? $cnt : '&nbsp;')."</span>
                <div class='ms-db-track'>
                    <div class='ms-db-bar {$barCls}' style='height:{$pct}%;min-height:{$minH}'></div>
                </div>
                <div class='ms-db-divider'></div>
                <span class='ms-db-day {$lblCls}'>{$day}</span>
                <span class='ms-db-dm'>{$dm}</span>
            </div>";
        })->join('');

        $total7 = $counts->sum();

        // Top 5 products
        $topProducts = [];
        Order::where('status', '!=', 'cancelled')->get(['items'])->each(function ($order) use (&$topProducts) {
            $items = is_array($order->items) ? $order->items : (json_decode($order->items ?? '[]', true) ?? []);
            foreach ($items as $item) {
                $name = $item['name'] ?? '—';
                $qty = (int) ($item['qty'] ?? 1);
                $topProducts[$name] = ($topProducts[$name] ?? 0) + $qty;
            }
        });
        arsort($topProducts);
        $topProducts = array_slice($topProducts, 0, 5, true);

        $rows = '';
        $rank = 1;
        foreach ($topProducts as $name => $qty) {
            $rows .= "<tr class='ms-db-row'>
                <td class='ms-db-rank'>{$rank}</td>
                <td class='ms-db-pname'>".htmlspecialchars($name)."</td>
                <td class='ms-db-qty'>{$qty} шт.</td>
            </tr>";
            $rank++;
        }

        $topInner = empty($topProducts)
            ? "<p class='ms-db-empty'>Ще немає замовлень</p>"
            : "<table class='ms-db-table'><tbody>{$rows}</tbody></table>";

        $css = '
<style>
.ms-db-col{display:flex;flex-direction:column;align-items:center;flex:1}
.ms-db-cnt{font-size:11px;font-weight:600;margin-bottom:4px;min-height:16px;color:var(--color-base-text)}
.ms-db-track{width:100%;flex:1;display:flex;align-items:flex-end}
.ms-db-bar{width:100%;border-radius:4px 4px 0 0;transition:height .4s ease}
.ms-db-bar--today{background:#422928}
.ms-db-bar--past{background:#c9a89a}
.ms-db-cnt.ms-db-bar--past{opacity:.6}
.ms-db-divider{width:100%;height:1px;background:var(--color-base-stroke);margin:0}
.ms-db-day{font-size:10px;margin-top:5px;line-height:1;color:var(--color-base-text)}
.ms-db-lbl--today{font-weight:700;color:#422928!important}
.ms-db-lbl--past{opacity:.55}
.ms-db-dm{font-size:10px;line-height:1.3;color:var(--color-base-text);opacity:.4}
.ms-db-chart-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.ms-db-chart-title{font-size:13px;font-weight:600;color:var(--color-base-text)}
.ms-db-chart-sub{font-size:11px;color:var(--color-base-text);opacity:.45;margin-top:2px}
.ms-db-chart-total{font-size:22px;font-weight:700;color:#422928}
.ms-db-bars{display:flex;gap:6px;align-items:stretch;height:100px}
.ms-db-top-title{font-size:13px;font-weight:600;color:var(--color-base-text);margin-bottom:12px}
.ms-db-table{width:100%;font-size:13px;border-collapse:collapse}
.ms-db-row td{padding:6px 0;border-bottom:1px solid var(--color-base-stroke)}
.ms-db-row:last-child td{border-bottom:none}
.ms-db-rank{color:var(--color-base-text);opacity:.35;width:24px;font-weight:600}
.ms-db-pname{color:var(--color-base-text)}
.ms-db-qty{text-align:right;font-weight:600;color:var(--color-base-text);white-space:nowrap}
.ms-db-empty{color:var(--color-base-text);opacity:.4;font-size:13px;padding:8px 0}
</style>';

        $chartHtml = $css."
        <div style='padding:20px 24px 16px'>
            <div class='ms-db-chart-header'>
                <div>
                    <div class='ms-db-chart-title'>Замовлення за 7 днів</div>
                    <div class='ms-db-chart-sub'>включаючи сьогодні</div>
                </div>
                <div class='ms-db-chart-total'>{$total7}</div>
            </div>
            <div class='ms-db-bars'>{$bars}</div>
        </div>";

        $topHtml = "
        <div style='padding:20px 24px 16px'>
            <div class='ms-db-top-title'>Топ-5 товарів</div>
            {$topInner}
        </div>";

        return [
            Grid::make([
                ValueMetric::make('Всього замовлень')
                    ->value(Order::count())
                    ->icon('shopping-bag')
                    ->columnSpan(3),

                ValueMetric::make('Нові (очікують)')
                    ->value(Order::where('status', 'pending')->count())
                    ->icon('bell')
                    ->columnSpan(3),

                ValueMetric::make('Замовлень сьогодні')
                    ->value(Order::whereDate('created_at', $today)->count())
                    ->icon('calendar')
                    ->columnSpan(3),

                ValueMetric::make('Виручка (₴)')
                    ->value(number_format((float) Order::where('status', '!=', 'cancelled')->sum('total'), 0, '.', ' '))
                    ->icon('banknotes')
                    ->columnSpan(3),
            ]),

            Grid::make([
                Column::make([
                    Box::make([FlexibleRender::make($chartHtml)]),
                ])->columnSpan(8),

                Column::make([
                    Box::make([FlexibleRender::make($topHtml)]),
                ])->columnSpan(4),
            ]),
        ];
    }
}
