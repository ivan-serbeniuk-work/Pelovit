<script setup lang="ts">
import { masterCategories, findCategory } from '~/data/masterCategories'

const config = useRuntimeConfig()
const route = useRoute()
const { imgSrc, addToCart, addedToCart } = useProduct()

const DEFAULT_CATEGORY = 'likuvalni-masazhi'

const category = computed(
  () => findCategory(String(route.query.category || '')) ?? findCategory(DEFAULT_CATEGORY)!
)

// Процедура з query, інакше — перша в категорії.
const procedure = computed(() => {
  const list = category.value.procedures
  return list.find(p => p.slug === String(route.query.procedure || '')) ?? list[0]
})

const tab = ref<'products' | 'protocol'>('products')
watch(procedure, () => { tab.value = 'products' })

const products = ref<any[]>([])

// На сторінці кілька препаратів одразу, тож ведемо в список товарів,
// а не у форму якогось одного.
useAdminEditTargets(() => ['product'])

// Картки препаратів тягнемо з каталогу за слагами процедури.
const { pending: productsPending } = await useAsyncData(
  () => `master-products-${procedure.value?.slug ?? 'none'}`,
  async () => {
    const slugs = procedure.value?.productSlugs ?? []
    const loaded = await Promise.all(
      slugs.map(slug =>
        $fetch<any>(`${config.public.apiBase}/products/${slug}`).catch(() => null)
      )
    )
    products.value = loaded.map(res => res?.product).filter(Boolean)
    return products.value
  },
  { watch: [procedure], default: () => [] }
)

const { siteUrl } = useRuntimeConfig().public
const canonicalUrl = computed(() => {
  const params = new URLSearchParams({ category: category.value.slug })
  if (procedure.value) params.set('procedure', procedure.value.slug)
  return `${siteUrl}/masters-category?${params.toString()}`
})

useHead({
  title: computed(() =>
    procedure.value
      ? `${procedure.value.title} — ${category.value.title} | Майстрам PELOVIT`
      : `${category.value.title} — Майстрам PELOVIT`
  ),
  link: computed(() => [{ rel: 'canonical', href: canonicalUrl.value }]),
  meta: computed(() => [
    {
      name: 'description',
      content:
        procedure.value?.description
        || `Відеопротоколи процедур «${category.value.title}» з продуктами Pelovit для майстрів салонів краси.`,
    },
  ]),
})

function procedureLink(slug: string) {
  return { path: '/masters-category', query: { category: category.value.slug, procedure: slug } }
}
</script>

<template>
<section class="py-5 text-white master_category-section">
  <div class="container">
    <div class="master_category_breadcrums">
      <div class="breadcrumb_master">
        <NuxtLink to="/masters" class="text-white text-decoration-none">Майстрам</NuxtLink>
        &gt; {{ category.title }}
      </div>
      <h1 class="fw-bold">{{ category.title }}</h1>
    </div>
  </div>
</section>

<section>
  <div class="master_category container py-4">
    <div class="row">
      <div class="col-lg-3">
        <div class="sidebar sticky-top" style="top: 20px;">
          <div v-if="category.procedures.length" class="nav flex-column">
            <NuxtLink
              v-for="item in category.procedures"
              :key="item.slug"
              :to="procedureLink(item.slug)"
              class="nav-link"
              :class="{ active: item.slug === procedure?.slug }"
            >{{ item.title }}</NuxtLink>
          </div>

          <p class="text-muted small mt-3 mb-2">Інші категорії</p>
          <div class="nav flex-column">
            <NuxtLink
              v-for="other in masterCategories.filter(c => c.slug !== category.slug)"
              :key="other.slug"
              :to="{ path: '/masters-category', query: { category: other.slug } }"
              class="nav-link"
            >{{ other.title }}</NuxtLink>
          </div>
        </div>
      </div>

      <div class="col-lg-9">
        <template v-if="procedure">
          <div v-if="procedure.youtubeId" class="hero-video mb-4">
            <iframe
              width="100%" height="550px"
              :src="`https://www.youtube.com/embed/${procedure.youtubeId}`"
              :title="procedure.title"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen
            ></iframe>
          </div>
          <div v-else class="hero-video video-placeholder mb-4">
            <p class="mb-0 text-muted">Відеопротокол цієї процедури готується</p>
          </div>

          <h2 class="display-5 fw-bold mb-3">{{ procedure.title }}</h2>
          <p v-if="procedure.description" class="lead text-muted mb-5">{{ procedure.description }}</p>

          <div class="tabs mb-5" role="tablist">
            <button
              class="tab"
              :class="{ active: tab === 'products' }"
              role="tab"
              :aria-selected="tab === 'products'"
              @click="tab = 'products'"
            >Препарати з відео</button>
            <button
              class="tab"
              :class="{ active: tab === 'protocol' }"
              role="tab"
              :aria-selected="tab === 'protocol'"
              @click="tab = 'protocol'"
            >Протокол процедури</button>
          </div>

          <section class="content_procedurs" id="procedurs">
            <div v-show="tab === 'products'" id="tab-products">
              <h5 class="mb-3">Препарати, які були використані</h5>

              <p v-if="productsPending" class="text-muted">Завантаження…</p>
              <p v-else-if="!products.length" class="text-muted">
                Підбірку препаратів для цієї процедури буде додано.
              </p>

              <div v-else class="row g-3 mb product_cards_sm">
                <div v-for="product in products" :key="product.id" class="col-md-4 product_card_sm">
                  <div class="cart-item d-flex align-items-center gap-3 bg-white p-2-5 rad-16">
                    <NuxtLink :to="`/product/${product.slug}`">
                      <img
                        :src="imgSrc(product.image)"
                        :alt="product.name"
                        class="product-img"
                        style="width: 50px;"
                        loading="lazy"
                        decoding="async"
                      >
                    </NuxtLink>
                    <div>
                      <h6>
                        <NuxtLink :to="`/product/${product.slug}`" class="text-decoration-none text-dark">
                          {{ product.name }}
                        </NuxtLink>
                      </h6>
                      <div class="content-product_card_sm">
                        <div class="fw-medium my-auto">{{ Math.round(product.price) }}₴</div>
                        <button class="btn buy rad-16" @click="addToCart(product)">
                          <span>{{ addedToCart[product.id] ? 'Додано!' : 'Купити' }}</span>
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6.33105 8H17.67C17.9584 7.99997 18.2434 8.06229 18.5054 8.1827C18.7674 8.30311 19.0003 8.47876 19.1881 8.6976C19.3759 8.91645 19.5141 9.17331 19.5933 9.45059C19.6726 9.72786 19.6909 10.019 19.647 10.304L18.392 18.456C18.2831 19.1644 17.9241 19.8105 17.38 20.2771C16.836 20.7438 16.1428 21.0002 15.426 21H8.57405C7.85745 21 7.16453 20.7434 6.62068 20.2768C6.07683 19.8102 5.71797 19.1643 5.60905 18.456L4.35405 10.304C4.31022 10.019 4.32854 9.72786 4.40775 9.45059C4.48697 9.17331 4.62521 8.91645 4.81299 8.6976C5.00078 8.47876 5.23367 8.30311 5.49569 8.1827C5.75772 8.06229 6.04268 7.99997 6.33105 8Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V11" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-show="tab === 'protocol'" id="tab-protocol">
              <h5 class="mb-3">Протокол процедури</h5>
              <ol v-if="procedure.protocol?.length" class="protocol-steps">
                <li v-for="(step, i) in procedure.protocol" :key="i">{{ step }}</li>
              </ol>
              <p v-else class="text-muted">
                Покроковий протокол цієї процедури готується. Залиште заявку на сторінці
                <NuxtLink to="/masters">Майстрам</NuxtLink> — надішлемо його першими.
              </p>
            </div>
          </section>
        </template>

        <p v-else class="text-muted py-5">
          Протоколи для категорії «{{ category.title }}» готуються. Оберіть іншу категорію ліворуч
          або залиште заявку на сторінці <NuxtLink to="/masters">Майстрам</NuxtLink>.
        </p>
      </div>
    </div>
  </div>
</section>
</template>
