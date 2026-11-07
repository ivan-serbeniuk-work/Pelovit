<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomePromo;
use App\Models\Subscriber;
use App\Services\WelcomePromoIssuer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscribeController extends Controller
{
    public function __construct(private WelcomePromoIssuer $issuer) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $subscriber = Subscriber::where('email', $validated['email'])->first();

        if ($subscriber) {
            if (! $subscriber->is_active) {
                $subscriber->update([
                    'is_active' => true,
                    'name' => $validated['name'] ?? $subscriber->name,
                ]);
            }

            // Код видається раз на пошту. Якщо людина підписується вдруге,
            // віддаємо той самий — інакше знижку можна штампувати без кінця.
            return response()->json([
                'success' => true,
                'already_subscribed' => true,
                'promo_code' => $subscriber->welcome_promo_code,
            ]);
        }

        $subscriber = Subscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'is_active' => true,
        ]);

        $promo = $this->issuer->issue();
        $subscriber->update(['welcome_promo_code' => $promo->code]);

        // Синхронно й у try/catch, як і решта листів у проекті: воркера
        // черг у прод-образі немає, тож queue() не доїхав би нікуди.
        // Підписка важливіша за лист — її не відкочуємо через збій пошти.
        try {
            Mail::to($subscriber->email)->send(new WelcomePromo($promo, $subscriber->name));
        } catch (\Throwable $e) {
            Log::error('Welcome promo mail failed: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'already_subscribed' => false,
            'promo_code' => $promo->code,
            'discount_label' => $promo->discount_label,
            'expires_at' => $promo->expires_at?->toDateString(),
        ], 201);
    }
}
