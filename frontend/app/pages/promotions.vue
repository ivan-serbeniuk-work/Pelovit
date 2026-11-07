<script setup lang="ts">
useSeoPage({
  pageKey: 'promotions',
  fallbackTitle: 'Акції — PELOVIT',
  fallbackDescription: 'Товари зі знижкою від PELOVIT. Косметика за акційними цінами з доставкою по Україні.',
  canonicalPath: '/promotions',
})

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()

useAdminEditTargets(() => ['product'])

const { data } = await useFetch<any>(`${config.public.apiBase}/catalog`, {
  query: computed(() => ({ ...route.query, on_sale: 1 })),
})

const products = computed(() => data.value?.products ?? { data: [], last_page: 1, links: [] })

function goToPage(page: number) {
  router.push({ query: { ...route.query, page: page > 1 ? String(page) : undefined } })
}
</script>

<template>
  <section class="py-5 bg-light all_categories">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Акції</h1>
      </div>

      <p v-if="!products.data?.length" class="text-muted py-4">Наразі акційних товарів немає.</p>

      <div v-else class="row g-4">
        <div v-for="product in products.data" :key="product.id" class="col-md-3 col-6">
          <ProductCard :product="product" />
        </div>
      </div>

      <AppPagination
        :current="Number(products.current_page ?? 1)"
        :last="Number(products.last_page ?? 1)"
        aria-label="Сторінки акцій"
        @change="goToPage"
      />
    </div>
  </section>
</template>
