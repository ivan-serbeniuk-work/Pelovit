@if($name){{ $name }}, вітаємо!@else Вітаємо!@endif

Дякуємо за підписку на новини PELOVIT.

Ваш персональний промокод на зниження {{ $promo->discount_label }}:

    {{ $promo->code }}

@if($promo->min_order_total > 0)
Діє на замовлення від {{ (int) round((float) $promo->min_order_total) }}₴.
@endif
@if($promo->expires_at)
Промокод одноразовий і діє до {{ $promo->expires_at->format('d.m.Y') }}.
@else
Промокод одноразовий.
@endif

Введіть його в кошику під час оформлення замовлення.

Каталог: {{ config('app.frontend_url') }}/catalog

—
PELOVIT
