<?php

use App\Http\Controllers\Admin\EditorVideoUploadController;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Services\LiqPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Точка входу в оплату для мобільного застосунку.
 *
 * Capacitor відкриває платіж у зовнішньому браузері через Browser.open(), а той
 * уміє лише GET — тоді як LiqPay чекає POST з data + signature. Цей маршрут
 * віддає сторінку, яка сабмітить форму сама. Посилання підписане й короткоживуче,
 * інакше будь-хто міг би відкрити чужий платіж, знаючи лише id замовлення.
 */
Route::get('/pay/{order}', function (Order $order, LiqPayService $liqpay) {
    abort_unless($order->payment_method === 'liqpay', 404);
    abort_if($order->payment_status === 'paid', 410, 'Замовлення вже оплачене.');

    return response()
        ->view('liqpay.redirect', ['liqpay' => $liqpay->checkoutData($order, forApp: true)])
        ->header('Cache-Control', 'no-store');
})->middleware('signed')->name('liqpay.pay');

/*
 * Завантаження відео з редактора статті (TinyMCE, плагін media).
 * Під тією самою охороною, що й решта адмінських маршрутів: без неї
 * будь-хто міг би класти файли в public/videos.
 */
Route::post('/admin/editor/upload-video', EditorVideoUploadController::class)
    ->middleware(['moonshine', 'MoonShine\Laravel\Http\Middleware\Authenticate'])
    ->name('admin.editor.upload-video');

Route::get('/admin/orders/export-csv', function () {
    $orders = Order::orderBy('created_at', 'desc')->get();

    $bom = "\xEF\xBB\xBF";
    $csv = $bom.implode(';', ['ID', 'Імʼя', 'Прізвище', 'Телефон', 'Email', 'Місто', 'Відділення', 'Оплата', 'Статус оплати', 'Статус', 'Сума', 'Дата'])."\n";

    foreach ($orders as $order) {
        $csv .= implode(';', [
            $order->id,
            '"'.str_replace('"', '""', $order->first_name ?? '').'"',
            '"'.str_replace('"', '""', $order->last_name ?? '').'"',
            '"'.$order->phone.'"',
            '"'.$order->email.'"',
            '"'.str_replace('"', '""', $order->city ?? '').'"',
            '"'.str_replace('"', '""', $order->branch ?? '').'"',
            '"'.$order->payment_method.'"',
            '"'.$order->payment_status.'"',
            '"'.$order->status.'"',
            $order->total,
            $order->created_at->format('d.m.Y H:i'),
        ])."\n";
    }

    return response($csv)
        ->header('Content-Type', 'text/csv; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="orders-'.now()->format('Y-m-d').'.csv"');
})->middleware(['moonshine', 'MoonShine\Laravel\Http\Middleware\Authenticate'])->name('admin.orders.export');

Route::post('/admin/settings/save', function (Request $request) {
    $keys = [
        'phone', 'phone_2', 'phone_3', 'email',
        'instagram_url', 'facebook_url', 'telegram_url', 'viber_url', 'youtube_url',
        'app_store_url', 'google_play_url',
        'banner_text', 'min_free_shipping',
        'payment_card_number', 'payment_card_holder',
        'calc_min_batch_total', 'calc_production_days', 'calc_spread_percent',
        // Тексти сторінки «Про нас»
        'about_intro', 'about_awards_title', 'about_cert_title',
        // Global SEO
        'google_analytics_id', 'default_og_image', 'site_description',
        // Per-page SEO
        'seo_home_title', 'seo_home_description', 'seo_home_og_image',
        'seo_catalog_title', 'seo_catalog_description', 'seo_catalog_og_image',
        'seo_journal_title', 'seo_journal_description', 'seo_journal_og_image',
        'seo_contacts_title', 'seo_contacts_description', 'seo_contacts_og_image',
        'seo_about_title', 'seo_about_description', 'seo_about_og_image',
        'seo_opt_title', 'seo_opt_description', 'seo_opt_og_image',
        'seo_masters_title', 'seo_masters_description', 'seo_masters_og_image',
        'seo_contract_title', 'seo_contract_description', 'seo_contract_og_image',
    ];
    foreach ($keys as $key) {
        if ($request->has($key)) {
            Setting::set($key, $request->input($key, ''));
        }
    }

    return redirect()->back()->with('success', 'Налаштування збережено');
})->middleware(['moonshine', 'MoonShine\Laravel\Http\Middleware\Authenticate'])->name('admin.settings.save');

Route::get('/admin/subscribers/export-csv', function () {
    $bom = "\xEF\xBB\xBF";
    $csv = $bom.implode(';', ['ID', 'Email', 'Імʼя', 'Активний', 'Дата'])."\n";
    Subscriber::orderBy('created_at', 'desc')->get()->each(function ($s) use (&$csv) {
        $csv .= implode(';', [$s->id, $s->email, '"'.($s->name ?? '').'"', $s->is_active ? 'Так' : 'Ні', $s->created_at->format('d.m.Y')])."\n";
    });

    return response($csv)
        ->header('Content-Type', 'text/csv; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="subscribers-'.now()->format('Y-m-d').'.csv"');
})->middleware(['moonshine', 'MoonShine\Laravel\Http\Middleware\Authenticate'])->name('admin.subscribers.export');

Route::patch('/admin/leads/{lead}/status', function (Lead $lead, Request $request) {
    $request->validate(['status' => 'required|in:new,in_progress,done']);
    $lead->update(['status' => $request->status]);

    return response()->json(['success' => true]);
})->middleware(['moonshine', 'MoonShine\Laravel\Http\Middleware\Authenticate'])->name('admin.leads.status');
