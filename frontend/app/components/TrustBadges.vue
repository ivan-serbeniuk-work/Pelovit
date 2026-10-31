<script setup lang="ts">
// Блок довіри під кнопкою «Додати в кошик» — переноситься з макета.
// Статичний контент, іконки — інлайнові SVG у стилі проєкту (stroke $brown-700).
const BADGES = [
  {
    text: 'Після оформлення з вами зв\'яжеться продавець-консультант',
    svg: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" stroke="#422928" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  },
  {
    text: 'Здійснюєте оплату будь-яким зручним для вас способом',
    svg: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="2.5" y="5" width="19" height="14" rx="2" stroke="#422928" stroke-width="1.5"/><path d="M2.5 9.5h19" stroke="#422928" stroke-width="1.5"/><path d="M6 15.5h4" stroke="#422928" stroke-width="1.5" stroke-linecap="round"/></svg>',
  },
  {
    text: 'Отримуєте посилку Новою Поштою (в межах України)',
    svg: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="m12 2.5 8 4.5v9l-8 4.5-8-4.5v-9l8-4.5z" stroke="#422928" stroke-width="1.5" stroke-linejoin="round"/><path d="m4 7 8 4.5L20 7M12 11.5V21" stroke="#422928" stroke-width="1.5" stroke-linejoin="round"/></svg>',
  },
]
</script>

<template>
  <ul class="trust-badges">
    <li v-for="(badge, i) in BADGES" :key="i" class="trust-badge">
      <span class="trust-badge__icon" aria-hidden="true" v-html="badge.svg" />
      <span class="trust-badge__text">{{ badge.text }}</span>
    </li>
  </ul>
</template>
