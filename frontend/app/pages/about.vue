<script setup lang="ts">
const config = useRuntimeConfig()
const { assetUrl } = useAsset()

useSeoPage({
  pageKey: 'about',
  fallbackTitle: 'Про нас — PELOVIT',
  fallbackDescription: 'Аксімед — інноваційне підприємство з 1985 року. Розробляємо натуральну косметику на основі наукових досліджень.',
  canonicalPath: '/about',
})

// Контент сторінки переїхав у базу: показники, цінності та віхи історії
// редагуються в адмінці (розділ «Сторінка «Про нас»»), тексти блоків —
// у налаштуваннях. Раніше все це було захардкожене тут.
const { data } = await useFetch<any>(`${config.public.apiBase}/about`)
const { data: settings } = await useSettings()

useAdminEditTargets(() => [
  'about-founder', 'about-stat', 'about-value', 'about-milestone', 'about-section', 'settings',
])

const founder = computed<any | null>(() => data.value?.founder ?? null)
const stats = computed<any[]>(() => data.value?.stats ?? [])

// Вкладки блоку «Дослідження»: у макеті зліва список, праворуч фото
// й текст вибраного пункту. Досі це були посилання на «#» — клік нічого
// не робив, праворуч завжди висіло одне й те саме.
const sections = computed<any[]>(() => data.value?.sections ?? [])
const activeSectionId = ref<number | null>(null)
const activeSection = computed(() =>
  sections.value.find(x => x.id === activeSectionId.value) ?? sections.value[0] ?? null,
)
const values = computed<any[]>(() => data.value?.values ?? [])
const historyTop = computed<any[]>(() => data.value?.milestones?.top ?? [])
const historyBottom = computed<any[]>(() => data.value?.milestones?.bottom ?? [])

// Порожнє поле в налаштуваннях — лишається текст за замовчуванням,
// щоб сторінка не оголювалася, поки контент не заповнили.
const text = (key: string, fallback: string) => computed(() => settings.value?.[key] || fallback)

const intro = text('about_intro', 'Аксімед — це інноваційне підприємство, підрозділ Одеської регіональної академії наук. Він знаходиться у Одесі і працює з 1985 року. На базі підприємства науковцями академії багато років проводяться наукові дослідження, клінічні спостереження та розробляються запатентовані рецептури.')
const awardsTitle = text('about_awards_title', 'Робота Аксімед неодноразово відмічена нагородами')
const certTitle = text('about_cert_title', 'Аксімед має європейську сертифікацію GMP ISO 22716')

const watchUsShots = ['catname4.png', 'face_washing.png', 'catname5.png', 'catname3.png']
</script>

<template>
<section class="hero about-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-3 fw-bold mb-4">Про нас</h1>
        <p class="lead fs-5">{{ intro }}</p>
      </div>
      <div class="col-lg-5">
        <div v-if="stats.length" class="row g-4">
          <div v-for="stat in stats" :key="stat.id" class="col-6">
            <div class="stat-card stat-card--metric text-center">
              <div class="stat-number">{{ stat.value }}</div>
              <p class="mb-0 fw-medium">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-nagoroda">
  <div class="container p-5">
    <h3 class="title_nagoroda text-center mb-5">{{ awardsTitle }}</h3>
    <div class="nagoroda_img text-center">
      <img :src="assetUrl('images/nagorods.png')" alt="" class="img-fluid" loading="lazy" decoding="async">
    </div>
  </div>
</section>

<section v-if="sections.length" class="py-5 doslidgenja_section">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-4">
        <div class="sidebar-nav">
          <button
            v-for="section in sections"
            :key="section.id"
            type="button"
            :class="{ active: activeSection?.id === section.id }"
            :aria-pressed="activeSection?.id === section.id"
            @click="activeSectionId = section.id"
          >
            {{ section.title }}
          </button>
        </div>
      </div>
      <div class="col-lg-4">
        <img
          v-if="activeSection?.image"
          :key="activeSection.id"
          :src="assetUrl(activeSection.image)"
          :alt="activeSection.title"
          class="research-img img-fluid"
          loading="lazy"
          decoding="async"
        >
      </div>
      <div class="col-lg-4">
        <h2 class="section-title mb-4">{{ activeSection?.title }}</h2>
        <p v-if="activeSection?.text" class="lead">{{ activeSection.text }}</p>
      </div>
    </div>
  </div>
</section>

<section class="values_section py-5">
  <div class="container">
    <h2 class="text-center mb-5 display-5">Наша місія та цінності</h2>

    <div class="row g-5 align-items-center">
      <!-- Засновник. Поки картку не заповнили в адмінці, її немає зовсім:
           краще без блоку, ніж із заглушкою «Ім'я Прізвище». -->
      <div v-if="founder" class="col-lg-5">
        <div class="p-4 value-card">
          <img
            v-if="founder.photo"
            :src="assetUrl(founder.photo)"
            :alt="founder.name"
            class="founder-photo img-fluid mb-4"
            loading="lazy"
            decoding="async"
          >

          <h4 class="fw-bold">{{ founder.name }}</h4>
          <p v-if="founder.role" class="mb-3">{{ founder.role }}</p>

          <p v-if="founder.quote" class="fst-italic">«{{ founder.quote }}»</p>
        </div>
      </div>

      <!-- Цінності -->
      <div :class="founder ? 'col-lg-7' : 'col-12'">
        <div class="row g-4">
          <div v-for="value in values" :key="value.id" class="col-md-6">
            <div class="value-card text-center">
              <div class="value-icon" v-html="value.icon"></div>
              <h3 class="value-title fw-bold">{{ value.title }}</h3>
              <p>{{ value.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section v-if="historyTop.length || historyBottom.length" class="py-5 history_section">
  <div class="container">
    <h2 class="text-center display-5 mb-5">Наша історія</h2>

    <div class="timeline row g-5 text-center">
      <!-- Верхній ряд: фото над роком -->
      <div v-for="item in historyTop" :key="item.id" class="col-lg-4 timeline-item">
        <img v-if="item.image" :src="assetUrl(item.image)" :alt="item.year" class="img-fluid mb-4" loading="lazy" decoding="async">
        <div class="year">{{ item.year }}</div>
        <p class="mt-3">{{ item.text }}</p>
      </div>

      <div class="d-line m-0"></div>

      <!-- Нижній ряд: фото під текстом -->
      <div v-for="item in historyBottom" :key="item.id" class="col-lg-6 timeline-item m-0">
        <div class="year">{{ item.year }}</div>
        <p class="mt-3">{{ item.text }}</p>
        <img v-if="item.image" :src="assetUrl(item.image)" :alt="item.year" class="img-fluid mb-4" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>

<section class="kontractne_vyrobnyctvo_page cert-section">
  <div class="container">
    <h2 class="section-title text-center">{{ certTitle }}</h2>
    <div class="row g-5 justify-content-center">
      <div class="col-lg-5 col-md-6"><div class="cert-card"><img :src="assetUrl('images/sertificate1.png')" alt="Сертифікат GMP ISO 22716" class="cert-image" loading="lazy" decoding="async"></div></div>
      <div class="col-lg-5 col-md-6"><div class="cert-card"><img :src="assetUrl('images/sertificate2.png')" alt="Сертифікат GMP ISO 22716" class="cert-image" loading="lazy" decoding="async"></div></div>
    </div>
  </div>
</section>

<section class="watcheus_section">
  <div class="container">
    <div class="row g-4">
      <h2>Слідкуйте за нами</h2>

      <div v-for="shot in watchUsShots" :key="shot" class="col-md-3 col-6">
        <img :src="assetUrl(`images/${shot}`)" alt="" class="img-fluid" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>
</template>
