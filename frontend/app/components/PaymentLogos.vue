<script setup lang="ts">
// Логотипи платіжних систем біля онлайн-оплати — переноситься з макета,
// додає довіри на останньому кроці. Спрощені впізнавані марки.
</script>

<template>
  <div class="payment-logos" aria-hidden="true">
    <!-- Mastercard -->
    <svg class="payment-logos__item" viewBox="0 0 48 30" fill="none">
      <rect width="48" height="30" rx="4" fill="#F4F0E7"/>
      <circle cx="19" cy="15" r="8" fill="#EB001B"/>
      <circle cx="29" cy="15" r="8" fill="#F79E1B" fill-opacity="0.9"/>
      <path d="M24 9a8 8 0 0 0 0 12 8 8 0 0 0 0-12z" fill="#FF5F00"/>
    </svg>
    <!-- Visa -->
    <svg class="payment-logos__item" viewBox="0 0 48 30" fill="none">
      <rect width="48" height="30" rx="4" fill="#F4F0E7"/>
      <text x="24" y="20" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" font-style="italic" font-weight="700" fill="#1A1F71">VISA</text>
    </svg>
    <!-- Apple Pay -->
    <svg class="payment-logos__item" viewBox="0 0 48 30" fill="none">
      <rect width="48" height="30" rx="4" fill="#F4F0E7"/>
      <text x="24" y="20" text-anchor="middle" font-family="-apple-system, Arial, sans-serif" font-size="11" font-weight="600" fill="#1A1A1A"> Pay</text>
    </svg>
    <!-- Google Pay -->
    <svg class="payment-logos__item" viewBox="0 0 48 30" fill="none">
      <rect width="48" height="30" rx="4" fill="#F4F0E7"/>
      <text x="24" y="20" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="600" fill="#5F6368">G Pay</text>
    </svg>
  </div>
</template>
