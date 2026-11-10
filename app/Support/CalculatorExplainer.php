<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CalculatorOption;
use App\Models\CalculatorTier;
use App\Models\Setting;

/**
 * Пояснення до калькулятора контрактного виробництва для адмінки: формула,
 * що означає «значення» в кожній групі, і живий приклад розрахунку на
 * поточних даних.
 *
 * Чому воно взагалі існує: в адмінці лежать «Крем 1.2» і «Базова формула 1»
 * без жодного контексту, і зрозуміти, що з цього складається ціна, можна
 * було лише читаючи код вітрини.
 *
 * ВАЖЛИВО: формула дублює розрахунок у frontend/app/pages/
 * kontractne-vyrobnyctvo.vue — справжній калькулятор живе там, тут лише
 * пояснення. Якщо правите формулу на вітрині, правте і тут; числа
 * прикладу закріплені тестом CalculatorExplainerTest.
 */
class CalculatorExplainer
{
    /** Дефолти повзунків на вітрині — приклад мусить показувати те, що бачить клієнт. */
    public const EXAMPLE_VOLUME = 30;

    public const EXAMPLE_QUANTITY = 100;

    /** Верхня межа повзунка тиражу на вітрині. */
    private const QTY_MAX = 2000;

    /** Що означає «значення» в кожній групі. */
    public const VALUE_MEANING = [
        'product' => ['₴ за 1 мл', 'Множиться на об\'єм: крем 1.2 × 30 мл = 36 ₴ рецептури.'],
        'formula' => ['множник', 'Множить рецептуру: 1 — без надбавки, 1.35 — дорожче на 35%.'],
        'packaging' => ['₴ за штуку', 'Додається до ціни одиниці як є, від об\'єму не залежить.'],
        'label' => ['₴ за штуку', 'Додається до ціни одиниці як є.'],
        'box' => ['₴ за штуку', 'Додається до ціни одиниці. «Ні» — це опція зі значенням 0.'],
    ];

    /** @var array<string, CalculatorOption|null> */
    private array $picked;

    /** @var list<array{min_quantity: int, discount_percent: float}> */
    private array $tiers;

    public function __construct()
    {
        $options = CalculatorOption::active()->get()->groupBy('group');

        // Вітрина сама обирає перший варіант кожної групи — приклад робить так само.
        $this->picked = [];

        foreach (array_keys(CalculatorOption::GROUPS) as $group) {
            $this->picked[$group] = $options->get($group, collect())->first();
        }

        $this->tiers = CalculatorTier::orderBy('min_quantity')
            ->get(['min_quantity', 'discount_percent'])
            ->map(fn (CalculatorTier $t) => [
                'min_quantity' => $t->min_quantity,
                'discount_percent' => $t->discount_percent,
            ])
            ->all();
    }

    public function minBatchTotal(): float
    {
        return (float) Setting::get('calc_min_batch_total', 0);
    }

    public function spreadPercent(): float
    {
        return (float) Setting::get('calc_spread_percent', 0);
    }

    public function productionDays(): int
    {
        return (int) Setting::get('calc_production_days', 0);
    }

    /** Чи є з чого рахувати: без продукту й формули вітрина ціну не показує. */
    public function hasPricing(): bool
    {
        return $this->picked['product'] !== null && $this->picked['formula'] !== null;
    }

    /** Ціна однієї одиниці до знижки за тираж. */
    public function baseUnitPrice(int $volume = self::EXAMPLE_VOLUME): float
    {
        if (! $this->hasPricing()) {
            return 0.0;
        }

        $recipe = $this->picked['product']->value * $volume * $this->picked['formula']->value;

        $materials = ($this->picked['packaging']->value ?? 0)
            + ($this->picked['label']->value ?? 0)
            + ($this->picked['box']->value ?? 0);

        return $recipe + $materials;
    }

    /** Найвища зі ступенів, поріг якої пройдено. */
    public function discountFor(int $quantity): float
    {
        $passed = array_filter($this->tiers, fn (array $t) => $quantity >= $t['min_quantity']);

        if ($passed === []) {
            return 0.0;
        }

        usort($passed, fn (array $a, array $b) => $b['min_quantity'] <=> $a['min_quantity']);

        return (float) $passed[0]['discount_percent'];
    }

    public function unitPriceFor(int $quantity, int $volume = self::EXAMPLE_VOLUME): float
    {
        return $this->baseUnitPrice($volume) * (1 - $this->discountFor($quantity) / 100);
    }

    public function batchTotalFor(int $quantity, int $volume = self::EXAMPLE_VOLUME): float
    {
        return $this->unitPriceFor($quantity, $volume) * $quantity;
    }

    /**
     * Тираж, за якого партія дотягує до мінімальної суми. Ділення дає замалу
     * відповідь: на знайденому тиражі спрацьовує знижка, одиниця дешевіє і
     * сума знову провалюється під мінімум — тому підтягуємо, доки росте.
     */
    public function requiredQuantity(int $volume = self::EXAMPLE_VOLUME): int
    {
        $min = $this->minBatchTotal();

        if ($this->baseUnitPrice($volume) <= 0 || $min <= 0) {
            return 0;
        }

        $qty = (int) ceil($min / $this->unitPriceFor(self::EXAMPLE_QUANTITY, $volume));

        for ($i = 0; $i < 10; $i++) {
            $next = (int) ceil($min / $this->unitPriceFor($qty, $volume));

            if ($next <= $qty) {
                break;
            }

            $qty = $next;
        }

        return $qty;
    }

    public function money(float $value): string
    {
        return number_format(round($value), 0, '.', ' ');
    }

    /**
     * Ціна за штуку — з копійками: округлена до гривні вона розходиться
     * з сумою партії (60 × 100 ≠ 5 950) і приклад виглядає як помилка.
     */
    public function moneyPrecise(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ' '), '0'), '.');
    }

    /** Клієнт бачить не точну суму, а вилку ±spread. */
    public function range(float $value): string
    {
        $spread = $this->spreadPercent() / 100;

        return $spread > 0
            ? $this->money($value * (1 - $spread)).'–'.$this->money($value * (1 + $spread)).' ₴'
            : $this->money($value).' ₴';
    }

    /** Повний блок-пояснення для сторінок адмінки. */
    public function render(): string
    {
        return "<div style='font-size:13px;line-height:1.6'>"
            .$this->formulaBlock()
            .$this->groupsBlock()
            .$this->exampleBlock()
            .$this->tiersBlock()
            .'</div>';
    }

    private function formulaBlock(): string
    {
        return "<div style='margin-bottom:20px'>
            ".$this->heading('Як рахується ціна')."
            <pre style='margin:0;padding:14px 16px;background:var(--color-body2,var(--color-body));border-radius:8px;font-size:12.5px;line-height:1.7;white-space:pre-wrap;font-family:ui-monospace,SFMono-Regular,Menlo,monospace'>ціна за 1 шт = вид продукту × об'єм (мл) × складність формули
             + упаковка + етикетка + коробка
             − знижка за тираж

сума партії = ціна за 1 шт × кількість</pre>
            <p style='margin:10px 0 0;opacity:.7'>Перший рядок — рецептура, вона залежить від об'єму.
            Другий — витратні матеріали, вони однакові для будь-якого об'єму.
            Знижка застосовується вже до зібраної ціни одиниці.</p>
        </div>";
    }

    private function groupsBlock(): string
    {
        $rows = '';

        foreach (CalculatorOption::GROUPS as $group => $label) {
            [$unit, $comment] = self::VALUE_MEANING[$group];
            $option = $this->picked[$group];

            // У підписі групи вже є пояснення в дужках — у таблиці воно зайве.
            $name = trim((string) preg_replace('/\s*\(.*\)$/u', '', $label));

            $current = $option !== null
                ? '<b>'.e($option->name).'</b> = '.$this->number($option->value)
                : "<span style='opacity:.5'>немає активних</span>";

            $rows .= "<tr>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>{$name}</td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke);white-space:nowrap'><code>{$unit}</code></td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>{$current}</td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke);opacity:.7'>{$comment}</td>
            </tr>";
        }

        return "<div style='margin-bottom:20px'>
            ".$this->heading('Що означає «значення» в кожній групі')."
            <div style='overflow-x:auto;-webkit-overflow-scrolling:touch'>
            <table style='width:100%;min-width:520px;border-collapse:collapse;font-size:12.5px'>
                <thead><tr style='text-align:left;opacity:.5;font-size:11px;text-transform:uppercase;letter-spacing:.04em'>
                    <th style='padding:0 10px 6px'>група</th>
                    <th style='padding:0 10px 6px'>одиниця</th>
                    <th style='padding:0 10px 6px'>перший варіант (його бачить клієнт за замовчуванням)</th>
                    <th style='padding:0 10px 6px'>як впливає</th>
                </tr></thead>
                <tbody>{$rows}</tbody>
            </table>
            </div>
        </div>";
    }

    private function exampleBlock(): string
    {
        if (! $this->hasPricing()) {
            return "<div style='margin-bottom:20px;padding:12px 14px;border-radius:8px;background:rgba(239,68,68,.1)'>
                <b>Калькулятор на сайті не рахує.</b> Потрібна хоча б одна активна опція
                в групах «Вид продукту» і «Складність формули» — без них вітрина показує
                вибір без сум.
            </div>";
        }

        $volume = self::EXAMPLE_VOLUME;
        $qty = self::EXAMPLE_QUANTITY;

        $product = $this->picked['product'];
        $formula = $this->picked['formula'];
        $recipe = $product->value * $volume * $formula->value;
        $materials = ($this->picked['packaging']->value ?? 0)
            + ($this->picked['label']->value ?? 0)
            + ($this->picked['box']->value ?? 0);

        $base = $this->baseUnitPrice();
        $discount = $this->discountFor($qty);
        $unit = $this->unitPriceFor($qty);
        $total = $this->batchTotalFor($qty);

        $lines = [
            'рецептура: '.e($product->name).' '.$this->number($product->value)
                ." × {$volume} мл × ".$this->number($formula->value).' ('.e($formula->name).') = '
                .$this->moneyPrecise($recipe).' ₴',
            'матеріали: '.$this->materialsLine().' = '.$this->moneyPrecise($materials).' ₴',
            '<b>ціна за 1 шт: '.$this->moneyPrecise($base).' ₴</b>'
                .($discount > 0 ? ' − '.$this->number($discount).'% за тираж = '.$this->moneyPrecise($unit).' ₴' : ' (знижки на цьому тиражі ще немає)'),
            '<b>сума партії: '.$this->moneyPrecise($unit)." × {$qty} шт = ".$this->money($total).' ₴</b>',
        ];

        $spread = $this->spreadPercent();
        $shown = $spread > 0
            ? 'Клієнту показується вилка ±'.$this->number($spread)."%: <b>{$this->range($unit)}</b> за штуку
               і <b>{$this->range($total)}</b> за партію."
            : "Розкид вимкнений — клієнт бачить точні цифри: <b>{$this->moneyPrecise($unit)} ₴</b> за штуку
               і <b>{$this->money($total)} ₴</b> за партію.";

        return "<div style='margin-bottom:20px'>
            ".$this->heading("Приклад на поточних даних — {$volume} мл, {$qty} шт")."
            <div style='padding:14px 16px;background:var(--color-body2,var(--color-body));border-radius:8px'>
                <div style='font-size:12.5px;line-height:1.9'>".implode('<br>', $lines)."</div>
                <p style='margin:12px 0 0;padding-top:12px;border-top:1px solid var(--color-base-stroke);opacity:.85'>{$shown}</p>
                {$this->minimumNote($total)}
            </div>
            <p style='margin:8px 0 0;opacity:.6;font-size:12px'>Це розрахунок за замовчуванням:
            об'єм і тираж — початкові положення повзунків, опції — перші в кожній групі.
            Поміняєте значення — цифри тут одразу перелічаться.</p>
        </div>";
    }

    private function materialsLine(): string
    {
        $parts = [];

        foreach (['packaging', 'label', 'box'] as $group) {
            $option = $this->picked[$group];

            if ($option === null) {
                continue;
            }

            $parts[] = e($option->name).' '.$this->number($option->value);
        }

        return $parts === [] ? '0' : implode(' + ', $parts);
    }

    private function minimumNote(float $total): string
    {
        $min = $this->minBatchTotal();

        if ($min <= 0) {
            return "<p style='margin:8px 0 0;opacity:.7'>Мінімальна сума партії не задана —
                калькулятор ні про що не попереджає.</p>";
        }

        $upper = $total * (1 + $this->spreadPercent() / 100);

        if ($total >= $min) {
            return "<p style='margin:8px 0 0;opacity:.7'>Партія проходить мінімум
                ({$this->money($min)} ₴) — попередження не показується.</p>";
        }

        $required = $this->requiredQuantity();

        $verdict = $upper >= $min
            ? "сума на межі мінімальної ({$this->money($min)} ₴)"
            : "сума менша за мінімальну ({$this->money($min)} ₴)";

        $advice = $required > self::QTY_MAX
            ? 'потрібний тираж не влізає в повзунок — клієнту радимо писати менеджеру'
            : "клієнту пишемо: потрібно <b>{$required} шт</b>";

        return "<p style='margin:8px 0 0;padding:8px 10px;border-radius:6px;background:rgba(245,158,11,.14)'>
            На цьому прикладі {$verdict}, тому {$advice}.</p>";
    }

    private function tiersBlock(): string
    {
        if ($this->tiers === []) {
            return "<div style='opacity:.7'>Ступенів знижки немає — ціна однакова на будь-якому тиражі.</div>";
        }

        $rows = '';

        foreach ($this->tiers as $tier) {
            $qty = (int) $tier['min_quantity'];
            $price = $this->hasPricing()
                ? $this->moneyPrecise($this->unitPriceFor($qty)).' ₴'
                : '—';
            $batch = $this->hasPricing()
                ? $this->money($this->batchTotalFor($qty)).' ₴'
                : '—';

            $rows .= "<tr>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>від {$qty} шт</td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>−".$this->number((float) $tier['discount_percent'])."%</td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>{$price}</td>
                <td style='padding:7px 10px;border-top:1px solid var(--color-base-stroke)'>{$batch}</td>
            </tr>";
        }

        $days = $this->productionDays();

        $daysNote = $days > 0
            ? " Термін виготовлення, який показується в результаті: {$days} днів."
            : ' Термін виготовлення не заданий і в результаті не показується.';

        return '<div>
            '.$this->heading('Ступені знижки — і що вийде на кожній')."
            <div style='overflow-x:auto;-webkit-overflow-scrolling:touch'>
            <table style='width:100%;min-width:520px;border-collapse:collapse;font-size:12.5px'>
                <thead><tr style='text-align:left;opacity:.5;font-size:11px;text-transform:uppercase;letter-spacing:.04em'>
                    <th style='padding:0 10px 6px'>тираж</th>
                    <th style='padding:0 10px 6px'>знижка</th>
                    <th style='padding:0 10px 6px'>ціна за шт</th>
                    <th style='padding:0 10px 6px'>сума партії</th>
                </tr></thead>
                <tbody>{$rows}</tbody>
            </table>
            </div>
            <p style='margin:10px 0 0;opacity:.7'>Береться найвища ступінь, поріг якої пройдено:
            на 400 шт зі ступенями 300 і 500 діє та, що від 300.{$daysNote}</p>
        </div>";
    }

    private function heading(string $text): string
    {
        return "<div style='font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
            opacity:.45;margin-bottom:10px'>".e($text).'</div>';
    }

    /** Без зайвих нулів: 1.2 лишається 1.2, а 1.0 стає 1. */
    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ' '), '0'), '.');
    }
}
