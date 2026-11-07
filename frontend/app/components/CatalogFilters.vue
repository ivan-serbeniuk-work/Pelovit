<script setup lang="ts">
/**
 * Панель фільтрів каталогу. Одна й та сама розмітка рендериться двічі:
 * як сайдбар на десктопі та як offcanvas на мобільному, — тож логіка
 * живе тут, а не дублюється на сторінці.
 */
const props = defineProps<{
  categories: any[]
  brands: string[]
  priceRange?: { min: number, max: number }
  /** Панель рендериться двічі — префікс тримає id унікальними в межах сторінки. */
  idPrefix: string
}>()

const uid = (name: string) => `${props.idPrefix}-${name}`

const route = useRoute()
const router = useRouter()

const selectedCategory = computed(() => (route.query.category as string) ?? '')
const selectedBrand = computed(() => (route.query.brand as string) ?? '')

const minPrice = ref((route.query.min_price as string) ?? '')
const maxPrice = ref((route.query.max_price as string) ?? '')

// Бренд у каталозі поки один — фільтр, який нічого не відсіює, лише шумить.
const showBrands = computed(() => props.brands.length > 1)

const hasFilters = computed(() => ['category', 'brand', 'sort', 'min_price', 'max_price', 'q']
  .some(key => Boolean(route.query[key])))

// Ціну вводять в обох копіях панелі (сайдбар + offcanvas) і міняють
// кнопками «Скинути» — без синхронізації поля розʼїжджаються з URL.
watch(() => [route.query.min_price, route.query.max_price], ([min, max]) => {
  minPrice.value = (min as string) ?? ''
  maxPrice.value = (max as string) ?? ''
})

function pushQuery(patch: Record<string, any>) {
  router.push({ query: { ...route.query, ...patch, page: undefined } })
}

function toggleCategory(slug: string) {
  pushQuery({ category: selectedCategory.value === slug ? undefined : slug })
}

function toggleBrand(brand: string) {
  pushQuery({ brand: selectedBrand.value === brand ? undefined : brand })
}

function applyPrice() {
  pushQuery({
    min_price: minPrice.value || undefined,
    max_price: maxPrice.value || undefined,
  })
}

function resetFilters() {
  router.push({ path: '/catalog' })
}
</script>

<template>
  <div class="catalog-filters">
    <div class="mb-4">
      <h6 class="fw-semibold mb-3">Категорії</h6>

      <!-- Категорія вибирається одна: кнопки, а не чекбокси, які обіцяють
           множинний вибір і мовчки знімають попередню галку. -->
      <div v-for="cat in categories" :key="cat.id" class="mb-1">
        <button
          type="button"
          class="filter-option"
          :class="{ 'is-active': selectedCategory === cat.slug }"
          :aria-pressed="selectedCategory === cat.slug"
          :disabled="!cat.products_count"
          @click="toggleCategory(cat.slug)"
        >
          <span class="filter-option__name">{{ cat.name }}</span>
        </button>

        <div v-if="cat.children?.length" class="ps-2 mt-1">
          <button
            v-for="child in cat.children"
            :key="child.id"
            type="button"
            class="filter-option filter-option--sm"
            :class="{ 'is-active': selectedCategory === child.slug }"
            :aria-pressed="selectedCategory === child.slug"
            :disabled="!child.products_count"
            @click="toggleCategory(child.slug)"
          >
            <span class="filter-option__name">{{ child.name }}</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="showBrands" class="mb-4">
      <h6 class="fw-semibold mb-3">Бренд</h6>
      <button
        v-for="brand in brands"
        :key="brand"
        type="button"
        class="filter-option"
        :class="{ 'is-active': selectedBrand === brand }"
        :aria-pressed="selectedBrand === brand"
        @click="toggleBrand(brand)"
      >
        <span class="filter-option__name">{{ brand }}</span>
      </button>
    </div>

    <div class="mb-4">
      <h6 class="fw-semibold mb-3">Ціна (₴)</h6>
      <div class="d-flex gap-2 align-items-center">
        <input
          v-model="minPrice"
          type="number"
          class="form-control form-control-sm"
          :placeholder="priceRange ? String(priceRange.min) : 'від'"
          min="0"
          aria-label="Ціна від"
          @keyup.enter="applyPrice"
        >
        <span class="text-muted">—</span>
        <input
          v-model="maxPrice"
          type="number"
          class="form-control form-control-sm"
          :placeholder="priceRange ? String(priceRange.max) : 'до'"
          min="0"
          aria-label="Ціна до"
          @keyup.enter="applyPrice"
        >
        <button class="btn btn-sm btn-dark" @click="applyPrice">OK</button>
      </div>
      <p v-if="priceRange" class="filter-hint mt-2 mb-0">
        Товари від {{ priceRange.min }} до {{ priceRange.max }} ₴
      </p>
    </div>

    <button
      type="button"
      class="btn btn-outline-secondary w-100"
      :disabled="!hasFilters"
      @click="resetFilters"
    >
      Скинути всі фільтри
    </button>
  </div>
</template>
