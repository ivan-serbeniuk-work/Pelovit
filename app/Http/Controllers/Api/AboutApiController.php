<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutMilestone;
use App\Models\AboutStat;
use App\Models\AboutValue;
use Illuminate\Http\JsonResponse;

/**
 * Контент сторінки «Про нас». Текстові блоки живуть у налаштуваннях
 * (вітрина бере їх із /api/settings), тут — повторювані списки.
 */
class AboutApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'stats' => AboutStat::active()->get(['id', 'value', 'label']),
            'values' => AboutValue::active()->get(['id', 'title', 'text', 'icon']),
            // Віхи розкладені двома рядами, як у макеті, — щоб вітрина
            // не фільтрувала той самий список двічі.
            'milestones' => [
                'top' => AboutMilestone::active()->where('placement', 'top')->get(['id', 'year', 'text', 'image']),
                'bottom' => AboutMilestone::active()->where('placement', 'bottom')->get(['id', 'year', 'text', 'image']),
            ],
        ]);
    }
}
