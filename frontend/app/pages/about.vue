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

useAdminEditTargets(() => ['about-stat', 'about-value', 'about-milestone', 'settings'])

const stats = computed<any[]>(() => data.value?.stats ?? [])
const values = computed<any[]>(() => data.value?.values ?? [])
const historyTop = computed<any[]>(() => data.value?.milestones?.top ?? [])
const historyBottom = computed<any[]>(() => data.value?.milestones?.bottom ?? [])

// Порожнє поле в налаштуваннях — лишається текст за замовчуванням,
// щоб сторінка не оголювалася, поки контент не заповнили.
const text = (key: string, fallback: string) => computed(() => settings.value?.[key] || fallback)

const intro = text('about_intro', 'Аксімед — це інноваційне підприємство, підрозділ Одеської регіональної академії наук. Він знаходиться у Одесі і працює з 1985 року. На базі підприємства науковцями академії багато років проводяться наукові дослідження, клінічні спостереження та розробляються запатентовані рецептури.')
const awardsTitle = text('about_awards_title', 'Робота Аксімед неодноразово відмічена нагородами')
const researchTitle = text('about_research_title', 'Дослідження')
const researchText = text('about_research_text', 'Одеська регіональна академія наук (ОРАН) — одна з найстаріших організацій України, що займається біологією і медициною. Після аварії на Чорнобильській АЕС академія отримала держзамовлення на створення нового препарату з сильним лікувальним і відновлювальним ефектом.')
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

<section class="py-5 doslidgenja_section">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-4">
        <div class="sidebar-nav">
          <a href="#">Винахідник</a>
          <a href="#">Pelovit</a>
          <a href="#" class="active">Дослідження</a>
          <a href="#">Виробництво</a>
        </div>
      </div>
      <div class="col-lg-4">
        <img :src="assetUrl('images/science_in_laboratory.png')" alt="Дослідження в лабораторії" class="research-img img-fluid" loading="lazy" decoding="async">
      </div>
      <div class="col-lg-4">
        <h2 class="section-title mb-4">{{ researchTitle }}</h2>
        <p class="lead">{{ researchText }}</p>
      </div>
    </div>
  </div>
</section>

<section class="values_section py-5">
  <div class="container">
    <h2 class="text-center mb-5 display-5">Наша місія та цінності</h2>

    <div class="row g-5 align-items-center">
      <!-- Засновник -->
      <div class="col-lg-5">
        <div class="p-4 value-card">
          <img :src="assetUrl('images/skin_portrait.png')" alt="Ім'я Прізвище" class="founder-photo img-fluid mb-4" loading="lazy" decoding="async">

          <h4 class="fw-bold">Ім'я Прізвище</h4>
          <p class="mb-3">засновник компанії</p>

          <p class="fst-italic">
            «Коли ми починали роботу над Pelovit, головною ідеєю було поєднати
            унікальні властивості української природи з сучасними науковими
            розробками. Ми хотіли створювати продукти, яким можна довіряти
            щодня: безпечні, ефективні та доступні.»
          </p>
        </div>
      </div>

      <!-- Цінності -->
      <div class="col-lg-7">
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
