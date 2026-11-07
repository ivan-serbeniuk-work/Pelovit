<script setup lang="ts">
const props = defineProps<{
  product: {
    id: number
    name: string
    price: number | string
    old_price?: number | string | null
    discount_percent?: number | null
    image: string | null
    slug: string
    category?: { name: string; slug: string } | null
    brand?: string | null
  }
}>()

const { imgSrc, addToCart, toggleWishlist, addedToCart, wishlist } = useProduct()

const inWishlist = computed(() => wishlist.has(props.product.id))

const HEART_OUTLINE = `<svg width="23" height="21" viewBox="0 0 23 21" fill="none"><path d="M20.0152 10.764L11.2652 19.43L2.51516 10.764C1.93802 10.2024 1.48341 9.52732 1.17997 8.78138C0.876525 8.03544 0.730818 7.23475 0.752023 6.42973C0.773228 5.62471 0.960886 4.8328 1.30318 4.10387C1.64547 3.37494 2.13499 2.72477 2.7409 2.19432C3.34681 1.66386 4.05599 1.26461 4.82378 1.0217C5.59157 0.778794 6.40134 0.69749 7.20209 0.78291C8.00285 0.868331 8.77724 1.11862 9.47652 1.51803C10.1758 1.91744 10.7848 2.45731 11.2652 3.10364C11.7476 2.462 12.3573 1.92685 13.0561 1.53168C13.7549 1.13651 14.5277 0.889833 15.3262 0.807082C16.1248 0.724331 16.9318 0.807289 17.6967 1.05077C18.4617 1.29424 19.1682 1.69299 19.772 2.22207C20.3758 2.75114 20.8638 3.39914 21.2057 4.12552C21.5475 4.85189 21.7357 5.64101 21.7585 6.44347C21.7813 7.24593 21.6383 8.04447 21.3383 8.7891C21.0383 9.53373 20.5879 10.2084 20.0152 10.771" stroke="#422928" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`
const HEART_FILLED = `<svg width="23" height="21" viewBox="0 0 23 21" fill="none"><path d="M20.0152 10.764L11.2652 19.43L2.51516 10.764C1.93802 10.2024 1.48341 9.52732 1.17997 8.78138C0.876525 8.03544 0.730818 7.23475 0.752023 6.42973C0.773228 5.62471 0.960886 4.8328 1.30318 4.10387C1.64547 3.37494 2.13499 2.72477 2.7409 2.19432C3.34681 1.66386 4.05599 1.26461 4.82378 1.0217C5.59157 0.778794 6.40134 0.69749 7.20209 0.78291C8.00285 0.868331 8.77724 1.11862 9.47652 1.51803C10.1758 1.91744 10.7848 2.45731 11.2652 3.10364C11.7476 2.462 12.3573 1.92685 13.0561 1.53168C13.7549 1.13651 14.5277 0.889833 15.3262 0.807082C16.1248 0.724331 16.9318 0.807289 17.6967 1.05077C18.4617 1.29424 19.1682 1.69299 19.772 2.22207C20.3758 2.75114 20.8638 3.39914 21.2057 4.12552C21.5475 4.85189 21.7357 5.64101 21.7585 6.44347C21.7813 7.24593 21.6383 8.04447 21.3383 8.7891C21.0383 9.53373 20.5879 10.2084 20.0152 10.771" stroke="#422928" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#422928"/></svg>`
const CART_SVG = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6.33105 8H17.67C17.9584 7.99997 18.2434 8.06229 18.5054 8.1827C18.7674 8.30311 19.0003 8.47876 19.1881 8.6976C19.3759 8.91645 19.5141 9.17331 19.5933 9.45059C19.6726 9.72786 19.6909 10.019 19.647 10.304L18.392 18.456C18.2831 19.1644 17.9241 19.8105 17.38 20.2771C16.836 20.7438 16.1428 21.0002 15.426 21H8.57405C7.85745 21 7.16453 20.7434 6.62068 20.2768C6.07683 19.8102 5.71797 19.1643 5.60905 18.456L4.35405 10.304C4.31022 10.019 4.32854 9.72786 4.40775 9.45059C4.48697 9.17331 4.62521 8.91645 4.81299 8.6976C5.00078 8.47876 5.23367 8.30311 5.49569 8.1827C5.75772 8.06229 6.04268 7.99997 6.33105 8Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V11" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`
</script>

<template>
  <div class="product-card card border-0 shadow-sm rad-16">
    <div v-if="product.discount_percent" class="discount-badge">−{{ product.discount_percent }}%</div>
    <button
      class="like"
      :class="{ active: inWishlist }"
      style="background:none;border:none;cursor:pointer;"
      :aria-label="inWishlist ? `Видалити ${product.name} з обраного` : `Додати ${product.name} до обраного`"
      :aria-pressed="inWishlist"
      @click="toggleWishlist(product)"
    >
      <span v-html="inWishlist ? HEART_FILLED : HEART_OUTLINE"></span>
    </button>
    <NuxtLink :to="`/product/${product.slug}`">
      <img :src="imgSrc(product.image)" class="card-img-top" :alt="product.name" loading="lazy" decoding="async">
    </NuxtLink>
    <div class="card-body">
      <!-- h3, а не h6: секції на сторінках — h2, тож h6 ламав ієрархію заголовків.
           Вигляд не змінюється — розмір задано в .product-card .card-title. -->
      <h3 class="card-title">
        <NuxtLink :to="`/product/${product.slug}`" class="text-decoration-none text-dark">{{ product.name }}</NuxtLink>
      </h3>
      <div class="wrapper__price_buy">
        <div class="disc_price_wrapper">
          <h4 class="price">{{ Math.round(Number(product.price)) }}₴</h4>
          <div v-if="product.old_price" class="disc_price">{{ Math.round(Number(product.old_price)) }}₴</div>
        </div>
        <button class="btn buy rad-12" @click="addToCart(product)">
          <span>{{ addedToCart[product.id] ? '✓' : 'Купити' }}</span>
          <span v-if="!addedToCart[product.id]" v-html="CART_SVG"></span>
        </button>
      </div>
    </div>
  </div>
</template>
