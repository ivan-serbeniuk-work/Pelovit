<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PromoCode;

/**
 * Видає персональний одноразовий промокод за підписку.
 *
 * Параметри — у config/loyalty.php. Код завжди відсотковий і з лімітом
 * використання 1: це подарунок конкретній людині, а не загальна акція,
 * тож ділитися ним у чатах сенсу немає.
 */
class WelcomePromoIssuer
{
    /** Без схожих між собою символів: 0/O та 1/I/L людина переплутає. */
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const SUFFIX_LENGTH = 6;

    /** Скільки разів пробуємо, якщо код збігся з наявним. */
    private const MAX_ATTEMPTS = 10;

    public function issue(): PromoCode
    {
        $config = config('loyalty.welcome');
        $days = (int) $config['days'];

        return PromoCode::create([
            'code' => $this->uniqueCode((string) $config['prefix']),
            'type' => 'percent',
            'value' => (float) $config['percent'],
            'min_order_total' => (float) $config['min_order_total'] ?: null,
            'starts_at' => now(),
            'expires_at' => $days > 0 ? now()->addDays($days) : null,
            'usage_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);
    }

    private function uniqueCode(string $prefix): string
    {
        $prefix = $prefix === '' ? '' : strtoupper($prefix).'-';

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $code = $prefix.$this->randomSuffix();

            if (! PromoCode::whereRaw('UPPER(code) = ?', [$code])->exists()) {
                return $code;
            }
        }

        // Практично недосяжно (31^6 варіантів), але краще віддати довший
        // код, ніж кинути виняток і втратити підписку.
        return $prefix.$this->randomSuffix().$this->randomSuffix();
    }

    private function randomSuffix(): string
    {
        $out = '';

        for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
            $out .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $out;
    }
}
