<script setup lang="ts">
useSeoPage({
  pageKey: 'journal',
  fallbackTitle: 'Блог — PELOVIT',
  fallbackDescription: 'Корисні статті про косметику, здоров\'я шкіри та лікувальні процедури від PELOVIT.',
  canonicalPath: '/journal',
})

const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const route = useRoute()
const router = useRouter()

const { data } = await useFetch<any>(`${config.public.apiBase}/journal`, {
  // Саме computed, а не route.query: під час навігації роутер підставляє новий
  // об'єкт query, тож знімок лишався зі старими параметрами і дані не оновлювались.
  query: computed(() => route.query),
  // posts.links не використовуються (пагінація рахується з current_page/last_page),
  // а в payload сторінки вони їхали разом із неперекладеними ключами.
  transform: (res: any) => {
    if (res?.posts?.links) delete res.posts.links
    return res
  },
})

const posts = computed(() => data.value?.posts ?? { data: [], current_page: 1, last_page: 1 })
const featured = computed(() => data.value?.featured ?? [])
const categories = computed(() => data.value?.categories ?? [])

const currentCategory = computed(() => route.query.category as string ?? '')
const search = computed(() => route.query.search as string ?? '')

const currentPage = computed(() => Number(posts.value.current_page ?? 1))
const lastPage = computed(() => Number(posts.value.last_page ?? 1))

const total = computed(() => Number(posts.value.total ?? 0))
const hasFilters = computed(() => Boolean(currentCategory.value || search.value))

// Множина для «Знайдено N статей / статті / стаття».
function pluralPosts(n: number) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return 'стаття'
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return 'статті'
  return 'статей'
}

async function goToPage(page: number) {
  if (page < 1 || page > lastPage.value || page === currentPage.value) return
  await router.push({ query: { ...route.query, page: page > 1 ? String(page) : undefined } })
  // Без цього після кліку на «2» лишаєшся внизу сторінки й бачиш той самий підвал.
  document.getElementById('articles')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function clearSearch() {
  router.push({ query: { ...route.query, search: undefined, page: undefined } })
}

function resetFilters() {
  router.push({ path: '/journal' })
}

function submitSearch(event: Event) {
  const value = (event.target as HTMLFormElement).search.value.trim()
  // Категорію зберігаємо, сторінку скидаємо — інакше можна опинитися
  // на 3-й сторінці результату, де всього одна.
  router.push({
    query: { ...route.query, search: value || undefined, page: undefined },
  })
}

function postImg(post: any) {
  // Локальна заглушка замість picsum.photos: сторонній сервіс тягнув
  // випадкові фото не в тему і додавав зайвий домен у критичний шлях.
  return post.image ? assetUrl(post.image) : '/journal-placeholder.svg'
}
</script>

<template>
<header>
  <div class="container py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 journal-head">
      <h1 class="fw-bold mb-0">Блог</h1>
      <form class="d-flex journal-search" @submit.prevent="submitSearch">
        <div class="input-group">
          <span class="input-group-text"><AppIcon name="search" /></span>
          <input type="text" name="search" class="form-control" placeholder="Пошук по блогу" :value="search" aria-label="Пошук по блогу">
          <button type="submit" class="btn btn-dark px-4">Знайти</button>
        </div>
      </form>
    </div>
  </div>
</header>

<section v-if="featured.length > 0" class="container publications_box py-5">
  <h2 class="fs-5 mb-4">Останні публікації</h2>
  <div class="row g-4">
    <div v-for="post in featured" :key="post.id" class="col-lg-6">
      <NuxtLink :to="`/journal/${post.slug}`" class="text-decoration-none text-dark">
        <div class="card h-100">
          <div class="position-relative">
            <div class="card-img-top_wrapper">
              <img :src="postImg(post)" class="card-img-top" :alt="post.title" loading="lazy" decoding="async">
            </div>
            <span v-if="post.category" class="badge position-absolute px-3 py-2">{{ post.category }}</span>
          </div>
          <div class="card-body d-flex flex-column">
            <p class="text-muted small data">{{ post.formatted_date }}</p>
            <h3 class="card-title fs-5">{{ post.title }}</h3>
            <p v-if="post.excerpt" class="card-excerpt text-muted mb-0">{{ post.excerpt }}</p>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</section>

<section class="catalog_journal">
  <div class="container py-5">
    <div class="row">
      <div class="col-lg-3 mb-5">
        <h2 class="fs-5 mb-3">Категорії</h2>
        <div class="sidebar">
          <ul class="nav flex-column">
            <li class="nav-item">
              <NuxtLink
                :to="{ path: '/journal', query: { search: search || undefined } }"
                class="nav-link d-flex justify-content-between align-items-center gap-2"
                :class="{ active: !currentCategory }"
              >
                <span>Всі</span>
                <span class="cat-count">{{ categories.reduce((sum: number, c: any) => sum + c.total, 0) }}</span>
              </NuxtLink>
            </li>
            <li v-for="cat in categories" :key="cat.name" class="nav-item">
              <NuxtLink
                :to="{ path: '/journal', query: { category: cat.name, search: search || undefined } }"
                class="nav-link d-flex justify-content-between align-items-center gap-2"
                :class="{ active: currentCategory === cat.name }"
              >
                <span>{{ cat.name }}</span>
                <span class="cat-count">{{ cat.total }}</span>
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="col-lg-9">
        <!-- Скільки знайшли + активні фільтри, які можна зняти в один клік -->
        <div class="journal-results d-flex flex-wrap align-items-center gap-2 mb-4">
          <span class="text-muted">Знайдено {{ total }} {{ pluralPosts(total) }}</span>

          <button v-if="currentCategory" type="button" class="filter-chip" @click="resetFilters">
            {{ currentCategory }}
            <span aria-hidden="true">✕</span>
            <span class="visually-hidden">Скинути категорію</span>
          </button>

          <button v-if="search" type="button" class="filter-chip" @click="clearSearch">
            Пошук: {{ search }}
            <span aria-hidden="true">✕</span>
            <span class="visually-hidden">Скинути пошук</span>
          </button>
        </div>

        <div v-if="!posts.data?.length" class="journal-empty text-center py-5">
          <p class="mb-2 fw-medium">Публікацій не знайдено.</p>
          <p class="text-muted mb-4">Спробуйте змінити запит або подивіться всі статті.</p>
          <button v-if="hasFilters" type="button" class="btn btn-dark px-4" @click="resetFilters">
            Скинути фільтри
          </button>
        </div>

        <div v-else class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="articles">
          <div v-for="post in posts.data" :key="post.id" class="col">
            <NuxtLink :to="`/journal/${post.slug}`" class="text-decoration-none text-dark">
              <div class="card h-100">
                <div class="position-relative">
                  <div class="card-img-top_wrapper">
                    <img :src="postImg(post)" class="card-img-top" :alt="post.title" loading="lazy" decoding="async">
                  </div>
                  <span v-if="post.category" class="badge position-absolute top-3 start-3">{{ post.category }}</span>
                </div>
                <div class="card-body">
                  <p class="text-muted small">{{ post.formatted_date }}</p>
                  <h3 class="card-title fs-6">{{ post.title }}</h3>
                  <p v-if="post.excerpt" class="card-excerpt text-muted mb-0">{{ post.excerpt }}</p>
                </div>
              </div>
            </NuxtLink>
          </div>
        </div>

        <AppPagination
          :current="currentPage"
          :last="lastPage"
          aria-label="Сторінки статей"
          @change="goToPage"
        />
      </div>
    </div>
  </div>
</section>
</template>
