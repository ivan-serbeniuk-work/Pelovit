<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Thumbs } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/thumbs'
import PhotoSwipeLightbox from 'photoswipe/lightbox'
import 'photoswipe/style.css'

const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const route = useRoute()

const { data, error } = await useFetch<any>(`${config.public.apiBase}/products/${route.params.slug}`)

if (error.value) throw createError({ statusCode: 404, message: 'Товар не знайдено' })

const product = computed(() => data.value?.product)
const related = computed(() => data.value?.related ?? [])

const { siteUrl } = useRuntimeConfig().public
const canonicalUrl = computed(() => `${siteUrl}/product/${route.params.slug}`)

const seoTitle = computed(() =>
  product.value?.meta_title || `${product.value?.name ?? 'Товар'} — PELOVIT`
)
const descriptionHtml = computed(() => sanitizeHtml(product.value?.description))
const instructionHtml = computed(() => sanitizeHtml(product.value?.instruction))
const instructionOpen = ref(false)

const seoDesc = computed(() =>
  product.value?.meta_description || htmlToText(product.value?.description) || `${product.value?.name} — косметика PELOVIT. Замовляйте з доставкою по Україні.`
)
const seoOgTitle = computed(() =>
  product.value?.og_title || product.value?.meta_title || `${product.value?.name} — PELOVIT`
)
const seoOgDesc = computed(() =>
  product.value?.og_description || product.value?.meta_description || htmlToText(product.value?.description) || ''
)

useHead({
  title: seoTitle,
  link: computed(() => [{ rel: 'canonical', href: canonicalUrl.value }]),
  meta: computed(() => [
    { name: 'description', content: seoDesc.value },
    ...(product.value?.no_index ? [{ name: 'robots', content: 'noindex, nofollow' }] : []),
    { property: 'og:url', content: canonicalUrl.value },
    { property: 'og:title', content: seoOgTitle.value },
    { property: 'og:description', content: seoOgDesc.value },
    { property: 'og:image', content: product.value?.image ? assetUrl(product.value.image) : '' },
    { property: 'og:type', content: 'product' },
  ]),
  script: computed(() => product.value && !product.value.no_index ? [{
    type: 'application/ld+json',
    children: JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'Product',
      name: product.value?.name,
      description: seoDesc.value,
      image: product.value?.image ? assetUrl(product.value.image) : undefined,
      sku: String(product.value?.id ?? ''),
      brand: { '@type': 'Brand', name: product.value?.brand ?? 'PELOVIT' },
      offers: {
        '@type': 'Offer',
        url: canonicalUrl.value,
        price: product.value?.price,
        priceCurrency: 'UAH',
        availability: (product.value?.stock ?? 0) > 0
          ? 'https://schema.org/InStock'
          : 'https://schema.org/OutOfStock',
      },
    }),
  }] : []),
})

const { imgSrc, addToCart: addToCartFn, toggleWishlist: toggleWishlistFn, wishlist } = useProduct()

// Видно тільки тим, хто вже залогінений в адмінці.
useAdminEditTargets(() => [
  product.value?.id && `product:${product.value.id}`,
  product.value?.category?.id && `category:${product.value.category.id}`,
])

// Промо застосунку показуємо лише у вебі: усередині самого застосунку
// пропонувати його встановити безглуздо.
const { data: settings } = await useSettings()
const isApp = Boolean(config.public.isApp)

// Магазин визначаємо на клієнті: на сервері платформи відвідувача не видно,
// а різний href на SSR і в браузері ламає гідратацію.
const storeUrl = ref<string | null>(null)

onMounted(() => {
  const ua = navigator.userAgent
  const isIos = /iPad|iPhone|iPod/.test(ua)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
  const isAndroid = /Android/.test(ua)
  const s = settings.value

  storeUrl.value = (isIos
    ? s?.app_store_url
    : isAndroid
      ? s?.google_play_url
      : s?.app_store_url || s?.google_play_url) || null
})

// Поки застосунку немає в сторах, кнопка веде до бейджів у футері —
// там чесно написано «Незабаром», замість переходу в нікуди.
const appPromoHref = computed(() => storeUrl.value ?? '#app-badges')

const addedToCart = ref(false)
const thumbsSwiper = ref(null)
const mainSwiper = ref<any>(null)

function setThumbsSwiper(swiper: any) { thumbsSwiper.value = swiper }
function setMainSwiper(swiper: any) { mainSwiper.value = swiper }
function galleryPrev() { mainSwiper.value?.slidePrev() }
function galleryNext() { mainSwiper.value?.slideNext() }

const galleryImages = computed(() => {
  if (!product.value) return []
  const imgs: string[] = []
  if (product.value.image) imgs.push(assetUrl(product.value.image))
  if (Array.isArray(product.value.images)) {
    product.value.images.forEach((img: string) => {
      const src = assetUrl(img)
      if (!imgs.includes(src)) imgs.push(src)
    })
  }
  return imgs
})

const galleryDims = reactive<Record<string, { w: number; h: number }>>({})

function preloadDims(src: string) {
  if (!import.meta.client || galleryDims[src]) return
  const img = new Image()
  img.onload = () => { galleryDims[src] = { w: img.naturalWidth, h: img.naturalHeight } }
  img.src = src
}

watch(galleryImages, (imgs) => imgs.forEach(preloadDims), { immediate: true })

const pswpGalleryEl = ref<HTMLElement | null>(null)
let lightbox: PhotoSwipeLightbox | null = null

onMounted(() => {
  lightbox = new PhotoSwipeLightbox({
    gallery: pswpGalleryEl.value!,
    children: 'a.pswp-item',
    pswpModule: () => import('photoswipe'),
  })
  lightbox.init()
})

onBeforeUnmount(() => {
  lightbox?.destroy()
  lightbox = null
})

function handleAddToCart() {
  if (!product.value) return
  addToCartFn({ ...product.value }, 1)
  addedToCart.value = true
  setTimeout(() => { addedToCart.value = false }, 1500)
}

function handleToggleWishlist() {
  if (!product.value) return
  toggleWishlistFn(product.value)
}

const inWishlist = computed(() => product.value ? wishlist.has(product.value.id) : false)

function copyProductUrl() {
  navigator.clipboard.writeText(canonicalUrl.value)
}

const HEART_OUTLINE = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.5 4.04 3 5.5l7 7Z" stroke="#422928" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`
const HEART_FILLED = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.5 4.04 3 5.5l7 7Z" stroke="#422928" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#422928"/></svg>`
</script>

<template>
<div v-if="product" class="product_name_page" data-product-name="pelovit">
  <section class="container py-5">
    <div class="row g-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><NuxtLink to="/">Головна</NuxtLink></li>
          <li class="breadcrumb-item"><NuxtLink to="/catalog">Каталог</NuxtLink></li>
          <li v-if="product.category" class="breadcrumb-item">
            <NuxtLink :to="`/catalog?category=${product.category.slug}`">{{ product.category.name }}</NuxtLink>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ product.name }}</li>
        </ol>
      </nav>

      <div class="col-lg-5 left_content" ref="pswpGalleryEl">
        <div class="share_wrapper">
          <i class="share" role="button" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#shareModal">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><circle cx="17" cy="4" r="3" stroke="#333" stroke-width="1.5"/><circle cx="5" cy="11" r="3" stroke="#333" stroke-width="1.5"/><circle cx="17" cy="18" r="3" stroke="#333" stroke-width="1.5"/><path d="M8 9.5L14 5.5M8 12.5L14 16.5" stroke="#333" stroke-width="1.5" stroke-linecap="round"/></svg>
          </i>
          <button class="like" style="background:none;border:none;cursor:pointer;" @click="handleToggleWishlist">
            <span v-html="inWishlist ? HEART_FILLED : HEART_OUTLINE"></span>
          </button>
        </div>

        <!-- Галерея -->
        <template v-if="galleryImages.length > 1">
          <div class="product-gallery-wrap">
            <Swiper
              :modules="[Thumbs]"
              :thumbs="{ swiper: thumbsSwiper }"
              class="product-gallery-main mb-2"
              @swiper="setMainSwiper"
            >
              <SwiperSlide v-for="(img, i) in galleryImages" :key="i">
                <a
                  :href="img"
                  class="pswp-item"
                  :data-pswp-width="galleryDims[img]?.w || 1200"
                  :data-pswp-height="galleryDims[img]?.h || 1200"
                >
                  <img :src="img" :alt="product.name" class="img-fluid product-image shadow-sm rounded">
                </a>
              </SwiperSlide>
            </Swiper>
            <button class="gallery-nav-btn gallery-nav-prev" @click="galleryPrev" aria-label="Назад">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button class="gallery-nav-btn gallery-nav-next" @click="galleryNext" aria-label="Вперед">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <Swiper
            :modules="[Thumbs]"
            :slides-per-view="4"
            :space-between="8"
            watch-slides-progress
            @swiper="setThumbsSwiper"
            class="product-gallery-thumbs"
          >
            <SwiperSlide v-for="(img, i) in galleryImages" :key="i">
              <img :src="img" :alt="product.name + ' ' + (i + 1)" class="img-fluid rounded product-thumb-img" loading="lazy" decoding="async">
            </SwiperSlide>
          </Swiper>
        </template>

        <!-- Одне фото -->
        <div v-else class="text-center">
          <a
            :href="galleryImages[0] ?? assetUrl(null)"
            class="pswp-item"
            :data-pswp-width="galleryDims[galleryImages[0]]?.w || 1200"
            :data-pswp-height="galleryDims[galleryImages[0]]?.h || 1200"
          >
            <img
              :src="galleryImages[0] ?? assetUrl(null)"
              class="img-fluid product-image shadow-sm rounded"
              :alt="product.name"
            >
          </a>
        </div>
      </div>

      <div class="col-lg-7 right_content">
        <h1 class="fw-bold">{{ product.name }}</h1>
        <div v-if="product.category || product.brand" class="categories">
          <span v-if="product.category" class="product-tag">{{ product.category.name }}</span>
          <span v-if="product.brand" class="product-tag">{{ product.brand }}</span>
        </div>

        <div class="d-flex align-items-center gap-3 mb-3">
          <h2 class="price mb-0">{{ Math.round(product.price) }}₴</h2>
          <span v-if="product.old_price" class="text-muted text-decoration-line-through fs-5">{{ Math.round(product.old_price) }}₴</span>
          <span v-if="product.discount_percent" class="discount-badge discount-badge--inline">−{{ product.discount_percent }}%</span>
        </div>

        <div class="my-4 counter_cart_wrapper">
          <button class="btn btn-dark btn-lg px-5 add_incart" @click="handleAddToCart">
            {{ addedToCart ? 'Додано!' : 'Додати в кошик' }}
          </button>
          <a
            v-if="!isApp"
            :href="appPromoHref"
            :target="storeUrl ? '_blank' : undefined"
            :rel="storeUrl ? 'noopener' : undefined"
            class="btn btn-outline-dark btn-lg px-5 buy_in_oneclick"
          >
            Отримай 10% знижки у застосунку
          </a>
        </div>

        <TrustBadges />

        <div class="description_wrapper_dropdown">
          <template v-if="product.description">
            <h5 class="mt-5 mb-3">Опис</h5>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div class="product-description" v-html="descriptionHtml" />
          </template>

          <template v-if="product.youtube_id">
            <h5 class="mt-5 mb-3">Відео</h5>
            <div class="ratio ratio-16x9 product-video">
              <iframe
                :src="`https://www.youtube-nocookie.com/embed/${product.youtube_id}?rel=0`"
                :title="`Відео: ${product.name}`"
                loading="lazy"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
                class="rounded"
              />
            </div>
          </template>

          <!-- Повний текст інструкції: склад, активи, спосіб застосування. -->
          <template v-if="instructionHtml">
            <button
              type="button"
              class="instruction-toggle"
              :aria-expanded="instructionOpen"
              aria-controls="product-instruction"
              @click="instructionOpen = !instructionOpen"
            >
              <span>Інструкція із застосування</span>
              <span class="instruction-toggle__icon" :class="{ 'is-open': instructionOpen }">⌄</span>
            </button>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div
              v-show="instructionOpen"
              id="product-instruction"
              class="product-description product-instruction"
              v-html="instructionHtml"
            />
          </template>
        </div>

        <div class="container faq-container mt-5">
          <h2 class="mb-4">Відповіді на запитання</h2>
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#q1" aria-expanded="true">Що таке PELOVIT?</button>
              </h2>
              <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Асортимент включає лікувальні препарати, доглядову косметику та контрактне виробництво.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">Яка доставка?</button>
              </h2>
              <div id="q2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Доставляємо по всій Україні через Нову Пошту.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <section v-if="related.length > 0" class="py-5 all_categories often_bought">
        <div class="container">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">З цим товаром часто купують</h2>
            <NuxtLink :to="product.category ? `/catalog?category=${product.category.slug}` : '/catalog'" class="view-all">Переглянути більше</NuxtLink>
          </div>
          <div class="row g-4">
            <div v-for="rel in related" :key="rel.id" class="col-md-3 col-6">
              <ProductCard :product="rel" />
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>
</div>

<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Поділитися:</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="input-group mb-4">
          <input type="text" class="form-control bg-light border-0" :value="canonicalUrl" readonly>
          <button class="btn btn-copy-link px-4" type="button" @click="copyProductUrl">Скопіювати</button>
        </div>
        <div class="d-flex justify-content-center gap-3 share-social">
          <a :href="`https://t.me/share/url?url=${encodeURIComponent(canonicalUrl)}`" target="_blank" rel="noopener" aria-label="Поділитися в Telegram">
            <AppIcon name="telegram" />
          </a>
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(canonicalUrl)}`" target="_blank" rel="noopener" aria-label="Поділитися в Facebook">
            <AppIcon name="facebook" />
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
