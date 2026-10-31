<script setup lang="ts">
const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const route = useRoute()

const { data, error } = await useFetch<any>(`${config.public.apiBase}/journal/${route.params.slug}`)
if (error.value) throw createError({ statusCode: 404, message: 'Статтю не знайдено' })

const post = computed(() => data.value?.post)
const related = computed(() => data.value?.related ?? [])
const bodyHtml = computed(() => sanitizeHtml(post.value?.body))

const canonicalUrl = computed(() => `${config.public.siteUrl}/journal/${route.params.slug}`)

const seoTitle = computed(() =>
  post.value?.meta_title || `${post.value?.title ?? 'Стаття'} — Блог PELOVIT`
)
const seoDesc = computed(() =>
  post.value?.meta_description || post.value?.excerpt || post.value?.title || ''
)
const seoOgTitle = computed(() =>
  post.value?.og_title || post.value?.meta_title || post.value?.title || ''
)
const seoOgDesc = computed(() =>
  post.value?.og_description || post.value?.meta_description || post.value?.excerpt || ''
)

useHead({
  title: seoTitle,
  link: computed(() => [{ rel: 'canonical', href: canonicalUrl.value }]),
  meta: computed(() => [
    { name: 'description', content: seoDesc.value },
    ...(post.value?.no_index ? [{ name: 'robots', content: 'noindex, nofollow' }] : []),
    { property: 'og:url', content: canonicalUrl.value },
    { property: 'og:title', content: seoOgTitle.value },
    { property: 'og:description', content: seoOgDesc.value },
    { property: 'og:image', content: post.value?.image ? assetUrl(post.value.image) : '' },
    { property: 'og:type', content: 'article' },
    { property: 'article:published_time', content: post.value?.published_at },
  ]),
})

// Видно тільки тим, хто вже залогінений в адмінці.
const { adminEditUrl } = useAdminEditLink('post', computed(() => post.value?.id))

const currentUrl = ref('')
onMounted(() => { currentUrl.value = window.location.href })

function copyUrl() {
  navigator.clipboard.writeText(currentUrl.value)
}
</script>

<template>
<section v-if="post" class="article_section">
  <div class="container py-5">
    <a
      v-if="adminEditUrl"
      :href="adminEditUrl"
      target="_blank"
      rel="noopener"
      class="admin-edit-link"
    >
      Редагувати в адмінці
    </a>
    <h1 class="fw-bold mb-4">{{ post.title }}</h1>
    <div v-if="post.image" class="mb-4">
      <img :src="assetUrl(post.image)" class="img-fluid rounded-4 w-100 article-image" :alt="post.title">
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
      <div class="wrapper_date">
        <button v-if="post.category" class="btn rounded-pill px-4 lik_prof">{{ post.category }}</button>
        <div class="date_artc_wrapper">
          <span class="date_artc">Дата публікації</span>
          <div class="date">{{ post.formatted_date }}</div>
        </div>
      </div>
      <button class="btn btn-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#shareModal">
        Поділитись
      </button>
    </div>
    <section class="article_content mx-auto">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="bodyHtml"></div>
    </section>
  </div>
</section>

<section v-if="related.length > 0" class="container publications_box similar py-5">
  <h2 class="mb-4">Подібні публікації</h2>
  <div class="row g-4">
    <div v-for="rel in related" :key="rel.id" class="col-lg-4">
      <NuxtLink :to="`/journal/${rel.slug}`" class="text-decoration-none text-dark">
        <div class="card h-100">
          <div class="position-relative">
            <div class="card-img-top_wrapper">
              <img loading="lazy" decoding="async" :src="rel.image ? assetUrl(rel.image) : '/journal-placeholder.svg'"
                   class="card-img-top" :alt="rel.title">
            </div>
            <span v-if="rel.category" class="badge position-absolute px-3 py-2">{{ rel.category }}</span>
          </div>
          <div class="card-body d-flex flex-column">
            <p class="text-muted small data">{{ rel.formatted_date }}</p>
            <h5 class="card-title">{{ rel.title }}</h5>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</section>

<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Поділитися:</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="input-group mb-4">
          <input type="text" class="form-control bg-light border-0" :value="currentUrl" readonly>
          <button class="btn btn-primary px-4" type="button" @click="copyUrl">Скопіювати</button>
        </div>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
          <a :href="`https://t.me/share/url?url=${encodeURIComponent(currentUrl)}`" target="_blank" rel="noopener">Telegram</a>
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`" target="_blank" rel="noopener">Facebook</a>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
