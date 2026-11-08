<script setup lang="ts">
import type { SuggestCategory, SuggestProduct } from '~/composables/useSearchSuggest'

/**
 * Випадайка під полем пошуку. Сама нічого не вантажить і не вирішує:
 * список і активний рядок приходять зверху, клавіатуру обробляє той,
 * у кого інпут (SearchOverlay), — інакше фокус і стрілки розповзлися б
 * між двома компонентами.
 */
export type SuggestItem =
  | { kind: 'product', product: SuggestProduct }
  | { kind: 'category', category: SuggestCategory }
  | { kind: 'all' }

defineProps<{
  items: SuggestItem[]
  activeIndex: number
  loading: boolean
  query: string
  total: number
}>()

const emit = defineEmits<{ select: [item: SuggestItem], hover: [index: number] }>()

const { assetUrl } = useAsset()

function price(value: string | number) {
  return `${Math.round(Number(value))}₴`
}
</script>

<template>
<div class="search-suggest">
  <p v-if="loading" class="search-suggest__status" role="status">Шукаємо…</p>

  <p v-else-if="!items.length" class="search-suggest__status">
    Нічого не знайшли за запитом «{{ query }}»
  </p>

  <ul v-else id="search-suggest-list" class="search-suggest__list" role="listbox">
    <template v-for="(item, i) in items" :key="i">
      <li
        :id="`search-suggest-${i}`"
        role="option"
        :aria-selected="i === activeIndex"
        :class="['search-suggest__option', { 'is-active': i === activeIndex }]"
        @mousemove="emit('hover', i)"
        @mousedown.prevent="emit('select', item)"
      >
        <template v-if="item.kind === 'product'">
          <img
            :src="assetUrl(item.product.image)"
            :alt="item.product.name"
            class="search-suggest__thumb"
            loading="lazy"
            decoding="async"
          >
          <span class="search-suggest__name">{{ item.product.name }}</span>
          <span class="search-suggest__price">
            {{ price(item.product.price) }}
            <s v-if="item.product.old_price">{{ price(item.product.old_price) }}</s>
          </span>
        </template>

        <template v-else-if="item.kind === 'category'">
          <span class="search-suggest__badge">Категорія</span>
          <span class="search-suggest__name">{{ item.category.name }}</span>
        </template>

        <template v-else>
          <span class="search-suggest__name search-suggest__all">
            Показати всі результати ({{ total }})
          </span>
        </template>
      </li>
    </template>
  </ul>
</div>
</template>
