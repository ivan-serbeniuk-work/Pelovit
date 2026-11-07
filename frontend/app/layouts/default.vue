<template>
  <div>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
      <div class="container">
        <NuxtLink class="navbar-brand fw-bold" to="/">
          <span class="brand-wordmark">PELOVIT</span>
        </NuxtLink>

        <!-- Mobile-only: wishlist + cart always visible next to toggler -->
        <div class="d-flex d-lg-none gap-1 ms-auto me-1 align-items-center">
          <NuxtLink to="/wishlist" class="nav-icon text-dark position-relative" title="Обране">
            <ClientOnly><span v-if="wishlistCount > 0" class="cart-badge">{{ wishlistCount }}</span></ClientOnly>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.5 4.04 3 5.5l7 7Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </NuxtLink>
          <NuxtLink to="/cart" class="nav-icon position-relative text-decoration-none" title="Кошик">
            <ClientOnly><span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span></ClientOnly>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6.33105 8H17.67C17.9584 7.99997 18.2434 8.06229 18.5054 8.1827C18.7674 8.30311 19.0003 8.47876 19.1881 8.6976C19.3759 8.91645 19.5141 9.17331 19.5933 9.45059C19.6726 9.72786 19.6909 10.019 19.647 10.304L18.392 18.456C18.2831 19.1644 17.9241 19.8105 17.38 20.2771C16.836 20.7438 16.1428 21.0002 15.426 21H8.57405C7.85745 21 7.16453 20.7434 6.62068 20.2768C6.07683 19.8102 5.71797 19.1643 5.60905 18.456L4.35405 10.304C4.31022 10.019 4.32854 9.72786 4.40775 9.45059C4.48697 9.17331 4.62521 8.91645 4.81299 8.6976C5.00078 8.47876 5.23367 8.30311 5.49569 8.1827C5.75772 8.06229 6.04268 7.99997 6.33105 8Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V11" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </NuxtLink>
        </div>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Меню">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mx-auto gap-1">
            <li class="nav-item cat-blue radius">
              <NuxtLink class="nav-link wrapper" to="/catalog">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="1" y="1" width="7" height="7" rx="1.5" stroke="#ffffff" stroke-width="1.5"/><rect x="12" y="1" width="7" height="7" rx="1.5" stroke="#ffffff" stroke-width="1.5"/><rect x="1" y="12" width="7" height="7" rx="1.5" stroke="#ffffff" stroke-width="1.5"/><rect x="12" y="12" width="7" height="7" rx="1.5" stroke="#ffffff" stroke-width="1.5"/></svg>
                <span>Каталог</span>
              </NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/">Головна</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/promotions">Акції</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/journal">Блог</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/about">Про нас</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/kontractne-vyrobnyctvo">Контрактне виробництво</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/masters">Майстри</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/contacts">Контакти</NuxtLink>
            </li>
          </ul>

          <!-- Desktop-only action icons -->
          <div class="d-none d-lg-flex align-items-center gap-2">
            <button class="nav-icon text-dark border-0 bg-transparent p-0" title="Пошук" @click="openSearch">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#1A1A1A" stroke-width="1.5"/><path d="M16.5 16.5 21 21" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>

            <a v-if="phone" :href="`tel:${phone.replace(/\D/g, '')}`" class="nav-icon text-dark" :title="phone">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.22 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <NuxtLink to="/wishlist" class="nav-icon text-dark position-relative" title="Обране">
              <ClientOnly><span v-if="wishlistCount > 0" id="wishlist-count" class="cart-badge">{{ wishlistCount }}</span></ClientOnly>
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.5 4.04 3 5.5l7 7Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </NuxtLink>

            <NuxtLink to="/cart" class="nav-cart position-relative text-decoration-none" title="Кошик">
              <ClientOnly><span v-if="cartCount > 0" id="cart-count" class="cart-badge">{{ cartCount }}</span></ClientOnly>
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6.33105 8H17.67C17.9584 7.99997 18.2434 8.06229 18.5054 8.1827C18.7674 8.30311 19.0003 8.47876 19.1881 8.6976C19.3759 8.91645 19.5141 9.17331 19.5933 9.45059C19.6726 9.72786 19.6909 10.019 19.647 10.304L18.392 18.456C18.2831 19.1644 17.9241 19.8105 17.38 20.2771C16.836 20.7438 16.1428 21.0002 15.426 21H8.57405C7.85745 21 7.16453 20.7434 6.62068 20.2768C6.07683 19.8102 5.71797 19.1643 5.60905 18.456L4.35405 10.304C4.31022 10.019 4.32854 9.72786 4.40775 9.45059C4.48697 9.17331 4.62521 8.91645 4.81299 8.6976C5.00078 8.47876 5.23367 8.30311 5.49569 8.1827C5.75772 8.06229 6.04268 7.99997 6.33105 8Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V11" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span class="cart-label">Кошик</span>
            </NuxtLink>
          </div>

          <!-- Mobile-only: search + phone in collapsed menu -->
          <div class="d-flex d-lg-none border-top pt-3 pb-1 mt-2 gap-4">
            <button class="nav-menu-action border-0 bg-transparent p-0 d-flex align-items-center gap-2" @click="openSearch">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#1A1A1A" stroke-width="1.5"/><path d="M16.5 16.5 21 21" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round"/></svg>
              Пошук
            </button>
            <a v-if="phone" :href="`tel:${phone.replace(/\D/g, '')}`" class="nav-menu-action text-decoration-none d-flex align-items-center gap-2">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.22 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              {{ phone }}
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Search overlay -->
    <Teleport to="body">
      <Transition name="search-fade">
        <div v-if="searchOpen" class="search-overlay" @click.self="closeSearch">
          <div class="search-box">
            <form @submit.prevent="submitSearch" class="d-flex gap-2">
              <input
                ref="searchInput"
                v-model="searchQuery"
                type="text"
                class="form-control form-control-lg"
                placeholder="Пошук товарів..."
                autocomplete="off"
              >
              <button type="submit" class="btn btn-dark px-4">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="9" cy="9" r="6" stroke="#fff" stroke-width="1.5"/><path d="M13.5 13.5L17 17" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
              </button>
            </form>
            <button class="search-overlay-close" @click="closeSearch" aria-label="Закрити">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <main>
      <slot />
    </main>

    <section class="loyalty-footer py-5">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-7">
            <h2 class="fw-bold display-5 mb-3">Завантажуйте наш застосунок!</h2>
            <p class="lead text-muted">
              Каталог, кошик і відстеження замовлення — під рукою,<br>
              зі спеціальними пропозиціями першими.
            </p>
          </div>
          <div class="col-lg-5">
            <div id="app-badges" class="app-badges">
              <!-- Поки посилання не заповнене в налаштуваннях, кнопка неактивна:
                   краще «Незабаром», ніж перехід у нікуди. -->
              <a
                v-if="settings?.app_store_url"
                :href="settings.app_store_url"
                target="_blank"
                rel="noopener"
                class="app-badge"
              >
                <AppIcon name="apple" size="30" />
                <span class="app-badge__text">
                  <small>Завантажити в</small>
                  App Store
                </span>
              </a>
              <span v-else class="app-badge app-badge--soon">
                <AppIcon name="apple" size="30" />
                <span class="app-badge__text">
                  <small>Незабаром в</small>
                  App Store
                </span>
              </span>

              <a
                v-if="settings?.google_play_url"
                :href="settings.google_play_url"
                target="_blank"
                rel="noopener"
                class="app-badge"
              >
                <AppIcon name="google-play" size="30" />
                <span class="app-badge__text">
                  <small>Завантажити в</small>
                  Google Play
                </span>
              </a>
              <span v-else class="app-badge app-badge--soon">
                <AppIcon name="google-play" size="30" />
                <span class="app-badge__text">
                  <small>Незабаром в</small>
                  Google Play
                </span>
              </span>
            </div>
          </div>
        </div>

        <hr class="my-5 border-light">

        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <h3 class="fw-bold mb-4">PELOVIT</h3>
          </div>
          <div class="col-md-6 col-lg-3">
            <h3 class="footer-heading fw-semibold mb-3">Навігація</h3>
            <ul class="list-unstyled">
              <li class="mb-2"><NuxtLink to="/about" class="text-dark text-decoration-none">Про нас</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/catalog" class="text-dark text-decoration-none">Препарати</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/kontractne-vyrobnyctvo" class="text-dark text-decoration-none">Контрактне виробництво</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/opt" class="text-dark text-decoration-none">Опт закупівлі</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/masters" class="text-dark text-decoration-none">Майстрам</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/journal" class="text-dark text-decoration-none">Блог</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/contacts" class="text-dark text-decoration-none">Адреси магазинів</NuxtLink></li>
              <li><NuxtLink to="/obmin-ta-povernennya" class="text-dark text-decoration-none">Обмін та повернення</NuxtLink></li>
            </ul>
          </div>
          <div class="col-md-6 col-lg-3">
            <h3 class="footer-heading fw-semibold mb-3">Категорія товарів</h3>
            <ul class="list-unstyled">
              <li class="mb-2"><NuxtLink to="/catalog" class="text-dark text-decoration-none">Парфумована лінійка ART17</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/catalog" class="text-dark text-decoration-none">Лікувальні препарати</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/catalog" class="text-dark text-decoration-none">Доглядова косметика</NuxtLink></li>
              <li class="mb-2"><NuxtLink to="/catalog" class="text-dark text-decoration-none">PRO серія Майстер</NuxtLink></li>
              <li><NuxtLink to="/catalog" class="text-dark text-decoration-none">Комплекси</NuxtLink></li>
            </ul>
          </div>
          <div class="col-md-6 col-lg-3">
            <h3 class="footer-heading fw-semibold mb-3">Контакти</h3>
            <ul class="list-unstyled">
              <li v-if="settings?.email" class="mb-3">
                <a :href="`mailto:${settings.email}`" class="text-dark text-decoration-none">✉ {{ settings.email }}</a>
              </li>
              <li v-for="key in ['phone', 'phone_2', 'phone_3']" :key="key" class="mb-2">
                <a v-if="settings?.[key]" :href="`tel:${settings[key].replace(/\D/g, '')}`" class="text-dark text-decoration-none">
                  ☎ {{ settings[key] }}
                </a>
              </li>
            </ul>
            <div class="d-flex gap-3 mt-3">
              <a v-if="settings?.instagram_url" :href="settings.instagram_url" target="_blank" rel="noopener" class="text-dark fs-4" aria-label="Instagram">
                <AppIcon name="instagram" />
              </a>
              <a v-if="settings?.facebook_url" :href="settings.facebook_url" target="_blank" rel="noopener" class="text-dark fs-4" aria-label="Facebook">
                <AppIcon name="facebook" />
              </a>
              <a v-if="settings?.youtube_url" :href="settings.youtube_url" target="_blank" rel="noopener" class="text-dark fs-4" aria-label="YouTube">
                <AppIcon name="youtube" />
              </a>
              <a v-if="settings?.telegram_url" :href="settings.telegram_url" target="_blank" rel="noopener" class="text-dark fs-4" aria-label="Telegram">
                <AppIcon name="telegram" />
              </a>
            </div>
          </div>
        </div>

        <div class="text-center text-muted mt-5 pt-4 border-top">
          © Pelovit 2025. Політика конфіденційності. Умови використання. Політика cookie.
        </div>
      </div>
    </section>
  <AppToast />
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'

const { data: settings } = await useSettings()

const cartStore = useCartStore()
const wishlistStore = useWishlistStore()

const cartCount = computed(() => cartStore.count)
const wishlistCount = computed(() => wishlistStore.count)
const phone = computed(() => settings.value?.phone || '+38 (063) 309-03-03')

const gaId = computed(() => settings.value?.google_analytics_id || '')
useHead(computed(() => gaId.value ? {
  script: [
    { src: `https://www.googletagmanager.com/gtag/js?id=${gaId.value}`, async: true },
    { children: `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','${gaId.value}');` },
  ],
} : {}))

const config = useRuntimeConfig()

const router = useRouter()
const searchOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement | null>(null)

function openSearch() {
  searchOpen.value = true
  nextTick(() => searchInput.value?.focus())
}

function closeSearch() {
  searchOpen.value = false
  searchQuery.value = ''
}

function submitSearch() {
  const q = searchQuery.value.trim()
  if (!q) return
  closeSearch()
  router.push({ path: '/catalog', query: { q } })
}

onMounted(() => {
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSearch()
  })
})
</script>
