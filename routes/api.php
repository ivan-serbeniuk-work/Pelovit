<?php

use App\Http\Controllers\Api\AboutApiController;
use App\Http\Controllers\Api\AdminLinkController;
use App\Http\Controllers\Api\CalculatorApiController;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\ContactsApiController;
use App\Http\Controllers\Api\ContractApiController;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\JournalApiController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LiqPayCallbackController;
use App\Http\Controllers\Api\NovaPoshtaApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\OrderTrackController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\PromoCodeApiController;
use App\Http\Controllers\Api\SearchApiController;
use App\Http\Controllers\Api\SubscribeController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/settings', fn () => response()->json(Setting::allKeyed()));
Route::get('/home', [HomeApiController::class, 'index']);
Route::get('/catalog', [CatalogApiController::class, 'index']);
Route::get('/calculator', [CalculatorApiController::class, 'index']);
Route::get('/contract', [ContractApiController::class, 'index']);
Route::get('/products/{slug}', [ProductApiController::class, 'show']);
Route::get('/journal', [JournalApiController::class, 'index']);
Route::get('/journal/{slug}', [JournalApiController::class, 'show']);
Route::get('/contacts', [ContactsApiController::class, 'index']);
Route::get('/about', [AboutApiController::class, 'index']);

// Підказки пошуку: ендпоїнт смикається на кожне натискання клавіші,
// тож тротлінг тут обов'язковий, інакше це найдешевший спосіб покласти сайт.
Route::get('/search/suggest', [SearchApiController::class, 'suggest'])->middleware('throttle:120,1');

// Посилання на редагування в адмінці — потребує сесійної куки MoonShine.
// Сторінка просить одразу всі свої цілі: /admin/edit-links?targets=product:18,faq
Route::get('/admin/edit-links', [AdminLinkController::class, 'show'])->middleware('web');
Route::post('/orders', [OrderApiController::class, 'store']);
Route::get('/orders/track/{token}', [OrderTrackController::class, 'show'])
    ->middleware('throttle:30,1');
Route::post('/promo/validate', [PromoCodeApiController::class, 'validate']);
Route::post('/leads', [LeadController::class, 'store']);
Route::post('/subscribe', [SubscribeController::class, 'store']);
Route::get('/nova-poshta/cities', [NovaPoshtaApiController::class, 'cities']);
Route::get('/nova-poshta/warehouses', [NovaPoshtaApiController::class, 'warehouses']);
Route::post('/liqpay/callback', [LiqPayCallbackController::class, 'handle']);
