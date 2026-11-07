<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalculatorOption;
use App\Models\CalculatorTier;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class CalculatorApiController extends Controller
{
    public function index(): JsonResponse
    {
        $options = CalculatorOption::active()->get()->groupBy('group');

        $map = fn (string $group) => $options->get($group, collect())
            ->map(fn (CalculatorOption $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'value' => $o->value,
                'image' => $o->image,
            ])
            ->values();

        return response()->json([
            'products' => $map('product'),
            'formulas' => $map('formula'),
            'packagings' => $map('packaging'),
            'labels' => $map('label'),
            'boxes' => $map('box'),
            'tiers' => CalculatorTier::orderBy('min_quantity')
                ->get(['min_quantity', 'discount_percent']),
            'min_batch_total' => (float) Setting::get('calc_min_batch_total', 0),
            'production_days' => (int) Setting::get('calc_production_days', 0),
            // Ціна показується вилкою ±%: до узгодження рецептури точної цифри не існує.
            'spread_percent' => (float) Setting::get('calc_spread_percent', 0),
        ]);
    }
}
