<script setup lang="ts">
const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const route = useRoute()
const router = useRouter()

// Категорія живе в шляху: /catalog/likuvalni. Той самий файл обслуговує
// і /catalog — завдяки опціональному параметру [[category]].
const categorySlug = computed(() => (route.params.category as string) || '')

const { data, status } = await useFetch<any>(`${config.public.apiBase}/catalog`, {
  query: computed(() => ({ ...route.query, category: categorySlug.value || undefined })),
})

const isLoading = computed(() => status.value === 'pending')

const products = computed(() => data.value?.products ?? { data: [], last_page: 1, links: [] })
const categories = computed(() => data.value?.categories ?? [])
const brands = computed(() => data.value?.brands ?? [])
// Захист від масиву з бекенда: у ньому .sort — метод, а не фільтр.
const filters = computed<Record<string, string>>(() => {
  const f = data.value?.filters
  return f && !Array.isArray(f) ? f : {}
})

const priceRange = computed(() => data.value?.price_range)

// Вибрана категорія — потрібна і для SEO, і для дока адмінки.
const activeCategory = computed<any | null>(() => {
  const slug = filters.value.category
  if (!slug) return null
  for (const cat of categories.value) {
    if (cat.slug === slug) return cat
    const child = cat.children?.find((c: any) => c.slug === slug)
    if (child) return child
  }
  return null
})

const activeCategoryId = computed<number | undefined>(() => activeCategory.value?.id)

// Заголовок сторінки: назва категорії, запит пошуку або просто «Каталог».
const pageHeading = computed(() => {
  if (filters.value.q) return `Результати: «${filters.value.q}»`
  return activeCategory.value?.name || 'Каталог'
})

useSeoPage({
  pageKey: 'catalog',
  fallbackTitle: 'Каталог товарів — PELOVIT',
  fallbackDescription: 'Каталог косметики PELOVIT. Лікувальні препарати, доглядова косметика, парфумована лінійка ART17. Замовляйте з доставкою по Україні.',

  // Категорія отримує власний canonical. Раніше він вів на /catalog — тобто
  // ми самі казали Google, що категорія це копія каталогу, і вона не могла
  // боротися за свої запити. Сортування й сторінки, навпаки, лишаються
  // склеєними з базовою адресою: це той самий набір товарів.
  canonicalPath: () => activeCategory.value
    ? `/catalog/${activeCategory.value.slug}`
    : '/catalog',

  override: () => activeCategory.value
    ? {
        title: `${activeCategory.value.name} — купити в Україні | PELOVIT`,
        description: `${activeCategory.value.name} від PELOVIT: натуральний склад на основі мінералів Куяльницького лиману. Доставка по Україні.`,
      }
    : null,

  // Видача пошуку — нескінченні тонкі сторінки під кожен запит, їх не
  // індексуємо. Посилання з них усе одно обходимо (follow).
  robots: () => (filters.value.q ? 'noindex, follow' : null),
})

// Крихти: Головна → Каталог → Категорія.
useJsonLd(() => {
  const crumbs = [
    { name: 'Головна', path: '/' },
    { name: 'Каталог', path: '/catalog' },
  ]

  if (activeCategory.value) {
    crumbs.push({
      name: activeCategory.value.name,
      path: `/catalog/${activeCategory.value.slug}`,
    })
  }

  return breadcrumbList(crumbs, config.public.siteUrl)
})

useAdminEditTargets(() => [
  activeCategoryId.value && `category:${activeCategoryId.value}`,
  'product',
  !activeCategoryId.value && 'category',
])

function setSort(sort: string) {
  router.push({ query: { ...route.query, sort: sort || undefined, page: undefined } })
}

// Множина для «Знайдено N товарів / товари / товар».
function pluralProducts(n: number) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return 'товар'
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return 'товари'
  return 'товарів'
}

function goToPage(page: number) {
  router.push({ query: { ...route.query, page: page > 1 ? String(page) : undefined } })
}

function resetFilters() {
  router.push({ path: '/catalog' })
}

const SORT_LABELS: Record<string, string> = {
  featured: 'За популярністю',
  new: 'Новинки',
  price_asc: 'Ціна: від дешевих',
  price_desc: 'Ціна: від дорогих',
}

function categoryName(slug: string) {
  for (const cat of categories.value) {
    if (cat.slug === slug) return cat.name
    const child = cat.children?.find((c: any) => c.slug === slug)
    if (child) return child.name
  }
  return slug
}

/**
 * Активні фільтри винесені на сторінку окремими чіпами: раніше про них
 * говорила лише цифра на кнопці, і щоб побачити вибране, треба було
 * відкривати панель.
 */
const activeChips = computed(() => {
  const f = filters.value
  const chips: { key: string, label: string, patch?: Record<string, any>, path?: string }[] = []

  // Категорія не в query, тож її чіп веде на /catalog зі збереженням решти.
  if (f.category) chips.push({ key: 'category', label: categoryName(f.category), path: '/catalog' })
  if (f.brand) chips.push({ key: 'brand', label: f.brand, patch: { brand: undefined } })
  if (f.sort) chips.push({ key: 'sort', label: SORT_LABELS[f.sort] ?? f.sort, patch: { sort: undefined } })
  if (f.min_price) chips.push({ key: 'min_price', label: `від ${f.min_price}₴`, patch: { min_price: undefined } })
  if (f.max_price) chips.push({ key: 'max_price', label: `до ${f.max_price}₴`, patch: { max_price: undefined } })
  if (f.q) chips.push({ key: 'q', label: `«${f.q}»`, patch: { q: undefined } })

  return chips
})

function removeChip(chip: { patch?: Record<string, any>, path?: string }) {
  router.push({
    path: chip.path ?? route.path,
    query: { ...route.query, ...(chip.patch ?? {}), page: undefined },
  })
}

</script>

<template>
  <section class="category-section py-5 restrict--card-heigh">
    <div class="container">
      <div class="row cards">
        <div v-for="cat in categories" :key="cat.id" class="cat_card">
          <NuxtLink :to="`/catalog/${cat.slug}`" class="text-decoration-none d-block">
            <div class="category-card rounded-4 overflow-hidden" :class="{ active: filters.category === cat.slug }">
              <img :src="assetUrl(cat.image)" class="rounded-4 w-100" :alt="cat.name" loading="lazy" decoding="async">
              <p class="mt-3 fw-medium cat_name">{{ cat.name }}</p>
            </div>
          </NuxtLink>
          <div v-if="cat.children?.length && filters.category === cat.slug" class="subcategories mt-2 ps-2">
            <NuxtLink
              v-for="child in cat.children"
              :key="child.id"
              :to="`/catalog/${child.slug}`"
              class="badge text-decoration-none me-1 mb-1"
              :class="filters.category === child.slug ? 'bg-dark' : 'bg-secondary'"
            >{{ child.name }}</NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Offcanvas фільтри — лише для мобільних; на десктопі панель у сайдбарі -->
  <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Фільтри</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <CatalogFilters :categories="categories" :brands="brands" :price-range="priceRange" id-prefix="m" />
    </div>
  </div>

  <!-- Каталог -->
  <section class="py-5 bg-light all_categories">
    <div class="container">
      <div class="catalog-head d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1 class="fw-bold mb-0">{{ pageHeading }}</h1>
        <div
          class="filters d-lg-none"
          data-bs-toggle="offcanvas"
          data-bs-target="#filterOffcanvas"
          style="cursor:pointer;"
        >
          <div class="content">
            Фільтри
            <span v-if="activeChips.length" class="badge bg-dark">{{ activeChips.length }}</span>
          </div>
          <i class="icon_filter">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 4H10V10H4V4Z" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 4H20V10H14V4Z" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 14H10V20H4V14Z" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 17C14 17.7956 14.3161 18.5587 14.8787 19.1213C15.4413 19.6839 16.2044 20 17 20C17.7956 20 18.5587 19.6839 19.1213 19.1213C19.6839 18.5587 20 17.7956 20 17C20 16.2044 19.6839 15.4413 19.1213 14.8787C18.5587 14.3161 17.7956 14 17 14C16.2044 14 15.4413 14.3161 14.8787 14.8787C14.3161 15.4413 14 16.2044 14 17Z" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </i>
        </div>
      </div>

      <div v-if="activeChips.length" class="catalog-chips d-flex flex-wrap align-items-center gap-2 mb-4">
        <button
          v-for="chip in activeChips"
          :key="chip.key"
          type="button"
          class="catalog-chip"
          @click="removeChip(chip)"
        >
          {{ chip.label }}
          <span class="catalog-chip__x" aria-hidden="true">×</span>
          <span class="visually-hidden">Прибрати фільтр</span>
        </button>
        <button type="button" class="btn btn-link btn-sm text-muted p-0 ms-1" @click="resetFilters">
          Скинути все
        </button>
      </div>

      <div class="catalog-toolbar d-flex justify-content-between align-items-center gap-3 mb-3">
        <p class="text-muted small mb-0">
          <template v-if="products.total">Знайдено {{ products.total }} {{ pluralProducts(products.total) }}</template>
        </p>
        <label class="catalog-sort">
          <span class="catalog-sort__label">Сортувати:</span>
          <!-- selected на самих option, а не value на select: у SSR-розмітці
               value браузер ігнорує, і до гідрації видно «За замовчуванням». -->
          <select class="form-select form-select-sm" @change="setSort(($event.target as HTMLSelectElement).value)">
            <option value="" :selected="!filters.sort">За замовчуванням</option>
            <option
              v-for="(label, value) in SORT_LABELS"
              :key="value"
              :value="value"
              :selected="filters.sort === value"
            >{{ label }}</option>
          </select>
        </label>
      </div>

      <div class="row g-4">
        <aside class="col-lg-3 d-none d-lg-block">
          <div class="catalog-sidebar">
            <h5 class="fw-bold mb-3">Фільтри</h5>
            <CatalogFilters :categories="categories" :brands="brands" :price-range="priceRange" id-prefix="d" />
          </div>
        </aside>

        <div class="col-lg-9">
          <!-- Сітка тьмяніє, поки їде відповідь: інакше клік по фільтру
               виглядає так, ніби нічого не сталося. -->
          <div class="catalog-grid" :class="{ 'catalog-grid--loading': isLoading }">
            <div v-if="isLoading" class="catalog-grid__spinner" role="status" aria-live="polite">
              <span class="spinner-border text-secondary"></span>
              <span class="visually-hidden">Завантаження товарів</span>
            </div>

            <p v-if="!products.data?.length && !isLoading" class="text-muted py-4">
              За цими фільтрами товарів немає. Спробуйте прибрати частину умов.
            </p>

            <div v-else class="row g-4">
              <div v-for="product in products.data" :key="product.id" class="col-xl-4 col-md-6 col-6">
                <ProductCard :product="product" />
              </div>
            </div>
          </div>

          <AppPagination
            :current="Number(products.current_page ?? 1)"
            :last="Number(products.last_page ?? 1)"
            aria-label="Сторінки каталогу"
            @change="goToPage"
          />
        </div>
      </div>
    </div>
  </section>
</template>
