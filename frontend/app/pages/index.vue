<script setup lang="ts">
useSeoPage({
  pageKey: 'home',
  fallbackTitle: 'PELOVIT — Косметика для здорової шкіри',
  fallbackDescription: 'PELOVIT — натуральна косметика з мінералами Куяльницького лиману. Лікувальні препарати, догляд за шкірою, контрактне виробництво.',
  canonicalPath: '/',
})

const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const { data } = await useFetch<any>(`${config.public.apiBase}/home`)

// Головна зібрана з кількох ресурсів — док дасть список, а не одну кнопку.
useAdminEditTargets(() => ['banner', 'faq', 'product', 'category', 'post'])

const promotions = computed(() => data.value?.promotions ?? [])
const allProducts = computed(() => data.value?.allProducts ?? [])

// Картка товару в герої (перенесено з макета): перший featured, інакше перший
// зі списку. Показується оверлеєм у правій частині героя на десктопі.
const heroProduct = computed(() => promotions.value[0] ?? allProducts.value[0] ?? null)

// Запасний слайд, коли в базі немає жодного банера.
const fallbackBanner = {
  title: 'Краса починається зі здорової шкіри.',
  subtitle: 'Відкрийте для себе догляд для обличчя, що поєднує ефективність сучасних формул і силу натуральних компонентів.',
  image: null,
  button_text: 'Дивитися товари',
  link: '/catalog',
}
const categories = computed(() => data.value?.categories ?? [])
const latestPosts = computed(() => data.value?.latestPosts ?? [])
const banners = computed(() => data.value?.banners ?? [])
const faqs = computed<any[]>(() => data.value?.faqs ?? [])

const CART_SVG = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6.33105 8H17.67C17.9584 7.99997 18.2434 8.06229 18.5054 8.1827C18.7674 8.30311 19.0003 8.47876 19.1881 8.6976C19.3759 8.91645 19.5141 9.17331 19.5933 9.45059C19.6726 9.72786 19.6909 10.019 19.647 10.304L18.392 18.456C18.2831 19.1644 17.9241 19.8105 17.38 20.2771C16.836 20.7438 16.1428 21.0002 15.426 21H8.57405C7.85745 21 7.16453 20.7434 6.62068 20.2768C6.07683 19.8102 5.71797 19.1643 5.60905 18.456L4.35405 10.304C4.31022 10.019 4.32854 9.72786 4.40775 9.45059C4.48697 9.17331 4.62521 8.91645 4.81299 8.6976C5.00078 8.47876 5.23367 8.30311 5.49569 8.1827C5.75772 8.06229 6.04268 7.99997 6.33105 8Z" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11V6C9 5.20435 9.31607 4.44129 9.87868 3.87868C10.4413 3.31607 11.2044 3 12 3C12.7956 3 13.5587 3.31607 14.1213 3.87868C14.6839 4.44129 15 5.20435 15 6V11" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`
const ARROW_RIGHT = `<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17.3172 10.442L11.6922 16.067C11.5749 16.1843 11.4159 16.2502 11.25 16.2502C11.0841 16.2502 10.9251 16.1843 10.8078 16.067C10.6905 15.9498 10.6247 15.7907 10.6247 15.6249C10.6247 15.459 10.6905 15.2999 10.8078 15.1827L15.3664 10.6249H3.125C2.95924 10.6249 2.80027 10.559 2.68306 10.4418C2.56585 10.3246 2.5 10.1656 2.5 9.99986C2.5 9.8341 2.56585 9.67513 2.68306 9.55792C2.80027 9.44071 2.95924 9.37486 3.125 9.37486H15.3664L10.8078 4.81705C10.6905 4.69977 10.6247 4.54071 10.6247 4.37486C10.6247 4.20901 10.6905 4.04995 10.8078 3.93267C10.9251 3.8154 11.0841 3.74951 11.25 3.74951C11.4159 3.74951 11.5749 3.8154 11.6922 3.93267L17.3172 9.55767C17.3753 9.61572 17.4214 9.68465 17.4529 9.76052C17.4843 9.8364 17.5005 9.91772 17.5005 9.99986C17.5005 10.082 17.4843 10.1633 17.4529 10.2392C17.4214 10.3151 17.3753 10.384 17.3172 10.442Z" fill="#1A1A1A"/></svg>`

const reviewShots = ['vidguk1.jpg', 'vidguk2.jpg', 'vidguk3.jpg', 'vidguk4.jpg']

// Hero — це LCP, але картинка задана як background-image в inline-стилі, тож
// браузер знаходить її лише після парсингу CSS. Preload прибирає цю затримку.
const heroImage = computed(() => {
  const first = banners.value[0]
  return first?.image ? assetUrl(first.image) : assetUrl('images/gl_face.png')
})

useHead({
  link: [{ rel: 'preload', as: 'image', href: heroImage, fetchpriority: 'high' }],
})

function postImg(post: any) {
  return post.image ? assetUrl(post.image) : '/journal-placeholder.svg'
}

</script>

<template>
<!-- Hero Carousel -->
<section class="hero-carousel">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    <!-- Активну точку задаємо статично на першій кнопці, а не реактивно (i===0):
         реактивний :class конфліктує з каруселлю Bootstrap. Решта — через v-for. -->
    <div class="carousel-indicators" v-if="banners.length > 1">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Слайд 1"></button>
      <button v-for="(b, i) in banners.slice(1)" :key="b.id" type="button" data-bs-target="#heroCarousel" :data-bs-slide-to="i + 1" :aria-label="`Слайд ${i + 2}`"></button>
    </div>
    <div class="carousel-inner">
      <!-- Якщо банерів нема — один статичний слайд, каруселі немає. -->
      <div v-if="banners.length === 0" class="carousel-item active">
        <HeroSlide :banner="fallbackBanner" />
      </div>
      <!-- Банери: `active` на першому слайді задаємо статичним класом, щоб Vue ним
           не керував і не відкочував карусель на перший слайд. -->
      <template v-else>
        <div class="carousel-item active">
          <HeroSlide :banner="banners[0]" />
        </div>
        <div v-for="banner in banners.slice(1)" :key="banner.id" class="carousel-item">
          <HeroSlide :banner="banner" />
        </div>
      </template>
    </div>
    <template v-if="banners.length > 1">
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </template>
  </div>
  <!-- Картка featured-товару поверх фото (перенесено з макета). Оверлей у правій
       частині; на мобільному приховується, щоб не перекривати текст слайда. -->
  <div v-if="heroProduct" class="hero-product-overlay">
    <div class="container">
      <ProductCard :product="heroProduct" class="hero-product-card" />
    </div>
  </div>
  <!-- На мобільному оверлей приховано (перекривав би текст слайда); featured-товар
       показуємо окремим блоком одразу під героєм, щоб перший екран не втрачав
       картку з макета. d-lg-none — лише < 992px. -->
  <div v-if="heroProduct" class="hero-product-mobile container d-lg-none">
    <ProductCard :product="heroProduct" class="hero-product-card" />
  </div>
</section>

<!-- Promotions -->
<section v-if="promotions.length" class="py-5 bg-light promotions">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold">Акції</h2>
      <NuxtLink to="/catalog" class="view-all">Переглянути більше</NuxtLink>
    </div>
    <div class="row g-4">
      <div v-for="product in promotions" :key="product.id" class="col-md-3 col-6">
        <ProductCard :product="product" />
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="category-section py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold mb-4">Категорії</h2>
      <NuxtLink to="/catalog" class="view-all">Переглянути більше</NuxtLink>
    </div>
    <div class="row cards">
      <div v-for="category in categories" :key="category.id" class="cat_card">
        <NuxtLink :to="`/catalog?category=${category.slug}`" class="text-decoration-none text-dark">
          <div class="category-card">
            <img :src="assetUrl(category.image)" class="rounded-4 w-100" :alt="category.name" loading="lazy" decoding="async">
            <p class="mt-3 fw-medium cat_name">{{ category.name }}</p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</section>

<!-- All Products -->
<section class="py-5 bg-light all_categories">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold">Всі товари</h2>
      <NuxtLink to="/catalog" class="view-all">Переглянути більше</NuxtLink>
    </div>
    <!-- Карусель вантажиться після гідратації; SSR віддає звичайну сітку,
         щоб товари лишалися в HTML для пошуковиків і не було порожнього блоку. -->
    <ClientOnly>
      <LazyProductsCarousel :products="allProducts" />
      <template #fallback>
        <div class="row g-3">
          <div v-for="product in allProducts.slice(0, 4)" :key="product.id" class="col-6 col-md-4 col-lg-3">
            <ProductCard :product="product" />
          </div>
        </div>
      </template>
    </ClientOnly>
  </div>
</section>

<!-- Contact Production -->
<section class="py-5 text-white contact-production-section">
  <div class="container">
    <div class="row align-items-center contact_info">
      <div class="col-lg-6 content">
        <h3 class="fw-bold contact_info_head">Контрактне виробництво</h3>
        <p class="contact_info_text">Пропонуємо повний цикл виробництва косметики під вашим брендом — від розробки формули до готової продукції.</p>
        <NuxtLink to="/kontractne-vyrobnyctvo" class="btn btn-light rad-16 to_page">До сторінки</NuxtLink>
      </div>
      <div class="col-lg-6 text-end image-wrapper">
        <img :src="assetUrl('images/pexel-mart.jpg')" class="rounded-4 shadow" alt="Контрактне виробництво косметики" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>

<!-- Pelovit Liman -->
<section class="py-5">
  <div class="container">
    <div class="row align-items-center card_liman">
      <h2 class="fw-bold pelovit-content">Pelovit — натуральна сила Куяльницького лиману для здоров'я та краси вдома</h2>
      <ul class="list-unstyled features">
        <li>Натуральні компоненти</li>
        <li>Ефект санаторію без виходу з дому</li>
        <li>Продукти для тіла, обличчя та загального оздоровлення</li>
      </ul>
    </div>
  </div>
</section>

<!-- Benefits -->
<section class="benefits-section py-5">
  <div class="container">
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="icon-wrapper">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="#F7F4EE" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.6667 40H10L40 10L70 40H63.3333" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16.6665 40V63.3333C16.6665 65.1014 17.3689 66.7971 18.6191 68.0474C19.8694 69.2976 21.5651 70 23.3332 70H56.6665C58.4346 70 60.1303 69.2976 61.3806 68.0474C62.6308 66.7971 63.3332 65.1014 63.3332 63.3333V40" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M33.3335 40H46.6668V53.3333H33.3335V40Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="benefit-text"><h3>Курорт удома</h3><p>Натуральне відновлення з мінералами Куяльника.</p></div>
      </div>
      <div class="benefit-card">
        <div class="icon-wrapper">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.6665 70.0001C18.3332 55.0001 24.9998 43.3334 39.9998 36.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M30 59.9999C50.7266 59.9999 65 49.0399 66.6666 19.9999V13.3333H53.2866C23.2866 13.3333 13.3333 26.6666 13.2866 43.3333C13.2866 46.6666 13.2866 53.3333 19.9533 59.9999H29.9533H30Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="benefit-text"><h3>Натуральний склад</h3><p>Грязь, мінерали, олії, безпечні для шкіри.</p></div>
      </div>
      <div class="benefit-card">
        <div class="icon-wrapper">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.6667 70H63.3334" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20 60H26.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M23.3333 60V70" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M30 36.6667L40 46.6667L60 26.6667L50 16.6667L30 36.6667Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M35 41.6667L30 46.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M56.6667 10L66.6667 20" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M40 70.0001C44.1567 70.0005 48.2103 68.7057 51.5971 66.2958C54.984 63.8859 57.5358 60.4806 58.8978 56.5534C60.2598 52.6261 60.3643 48.3721 59.1968 44.3827C58.0293 40.3933 55.6477 36.8668 52.3833 34.2935" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="benefit-text"><h3>Українське виробництво</h3><p>10+ років досвіду та сертифікації.</p></div>
      </div>
      <div class="benefit-card">
        <div class="icon-wrapper">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M40 70C36.0603 70 32.1593 69.224 28.5195 67.7164C24.8797 66.2087 21.5726 63.999 18.7868 61.2132C16.001 58.4274 13.7913 55.1203 12.2836 51.4805C10.776 47.8407 10 43.9397 10 40C10 36.0603 10.776 32.1593 12.2836 28.5195C13.7913 24.8797 16.001 21.5726 18.7868 18.7868C21.5726 16.001 24.8797 13.7913 28.5195 12.2836C32.1593 10.776 36.0603 10 40 10C47.9565 10 55.5871 13.1607 61.2132 18.7868C66.8393 24.4129 70 32.0435 70 40C70 47.9565 66.8393 55.5871 61.2132 61.2132C55.5871 66.8393 47.9565 70 40 70Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M33.3333 33.3333C31.6666 29.9999 24.9999 29.9999 23.3333 33.3333" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M56.6667 33.3333C55.0001 29.9999 48.3334 29.9999 46.6667 33.3333" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M48.3334 50C47.2472 51.1087 45.9506 51.9894 44.5197 52.5907C43.0887 53.192 41.5522 53.5017 40.0001 53.5017C38.448 53.5017 36.9114 53.192 35.4805 52.5907C34.0496 51.9894 32.753 51.1087 31.6667 50" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="benefit-text"><h3>Видимий результат</h3><p>Детокс, омолодження та регенерація.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- Reviews -->
<section class="py-5 reviews-section">
  <div class="container">
    <div class="section-header">
      <h2>Відгуки</h2>
      <div class="tabs">
        <button class="tab">Огляди</button>
        <button class="tab active">Відгуки</button>
      </div>
    </div>

    <div class="reviews-grid">
      <div v-for="shot in reviewShots" :key="shot" class="review-card" :style="{ backgroundImage: `url('${assetUrl(`images/${shot}`)}')` }">
        <div class="review-image"></div>
        <div class="review-footer">
          <div class="wrapper rad-16">
            <div class="product-info">
              <img :src="assetUrl('images/vid_prep.png')" alt="Пеловіт" loading="lazy" decoding="async">
              <div class="content_wrapper">
                <div class="prep_name">
                  <div>Пеловіт Класичний</div>
                  <div>500мл</div>
                </div>
                <div class="rating">★★★★★</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Loyalty -->
<section class="py-5 text-white loyalty-section">
  <div class="container">
    <div class="loyalty-content">
      <div class="loyalty-text">
        <h2>Приєднуйтесь до нашої<br>програми лояльності!</h2>
        <p>Реєструйтесь на сайті та отримуйте бонуси за покупки,<br>
          спеціальні пропозиції та персональні знижки.</p>

        <button class="btn-register rad-16">Зареєструватись</button>
      </div>

      <div class="loyalty-benefits">
        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 26.6665C10 24.0143 11.0536 21.4708 12.9289 19.5954C14.8043 17.7201 17.3478 16.6665 20 16.6665H60C62.6522 16.6665 65.1957 17.7201 67.0711 19.5954C68.9464 21.4708 70 24.0143 70 26.6665V53.3332C70 55.9853 68.9464 58.5289 67.0711 60.4042C65.1957 62.2796 62.6522 63.3332 60 63.3332H20C17.3478 63.3332 14.8043 62.2796 12.9289 60.4042C11.0536 58.5289 10 55.9853 10 53.3332V26.6665Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M23.3333 53.3335L33.3333 43.3335L43.3333 53.3335" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M26.6667 43.3333C24.0367 43.3333 20 41.0933 20 38.3333C20 35.5733 22.37 33.3333 25 33.3333C28.76 33.2667 31.9233 37.2333 33.3333 43.3333C34.7433 37.2333 37.9067 33.2667 41.6667 33.3333C44.2967 33.3333 46.6667 35.5733 46.6667 38.3333C46.6667 41.0933 42.63 43.3333 40 43.3333H26.6667Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Бонуси за кожну покупку</h3>
        </div>

        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6.6667 30a10 10 0 1 1 0 20v6.6667a6.6667 6.6667 0 0 0 6.6667 6.6667h53.3333a6.6667 6.6667 0 0 0 6.6667-6.6667V50a10 10 0 1 1 0-20v-6.6667a6.6667 6.6667 0 0 0-6.6667-6.6667H13.3334A6.6667 6.6667 0 0 0 6.6667 23.3333V30Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M30 30L50 50" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M31.6667 33.3333C32.5871 33.3333 33.3333 32.5871 33.3333 31.6667C33.3333 30.7462 32.5871 30 31.6667 30C30.7462 30 30 30.7462 30 31.6667C30 32.5871 30.7462 33.3333 31.6667 33.3333Z" fill="#1A7B9E" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M48.3334 49.9998C49.2539 49.9998 50.0001 49.2536 50.0001 48.3332C50.0001 47.4127 49.2539 46.6665 48.3334 46.6665C47.4129 46.6665 46.6667 47.4127 46.6667 48.3332C46.6667 49.2536 47.4129 49.9998 48.3334 49.9998Z" fill="#1A7B9E" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </div>
          <h3>Персональні знижки</h3>
        </div>

        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 29.9998C10 29.1158 10.3512 28.2679 10.9763 27.6428C11.6014 27.0177 12.4493 26.6665 13.3333 26.6665H66.6667C67.5507 26.6665 68.3986 27.0177 69.0237 27.6428C69.6488 28.2679 70 29.1158 70 29.9998V36.6665C70 37.5506 69.6488 38.3984 69.0237 39.0235C68.3986 39.6486 67.5507 39.9998 66.6667 39.9998H13.3333C12.4493 39.9998 11.6014 39.6486 10.9763 39.0235C10.3512 38.3984 10 37.5506 10 36.6665V29.9998Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 26.6665V69.9998" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M63.3334 40V63.3333C63.3334 65.1014 62.631 66.7971 61.3808 68.0474C60.1306 69.2976 58.4349 70 56.6668 70H23.3334C21.5653 70 19.8696 69.2976 18.6194 68.0474C17.3691 66.7971 16.6667 65.1014 16.6667 63.3333V40" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M25.0001 26.6667C22.7899 26.6667 20.6703 25.7887 19.1075 24.2259C17.5447 22.6631 16.6667 20.5435 16.6667 18.3333C16.6667 16.1232 17.5447 14.0036 19.1075 12.4408C20.6703 10.878 22.7899 9.99999 25.0001 9.99999C28.2157 9.94397 31.3668 11.5042 34.0425 14.4772C36.7182 17.4502 38.7943 21.698 40.0001 26.6667C41.2058 21.698 43.2819 17.4502 45.9576 14.4772C48.6333 11.5042 51.7845 9.94397 55.0001 9.99999C57.2102 9.99999 59.3298 10.878 60.8926 12.4408C62.4554 14.0036 63.3334 16.1232 63.3334 18.3333C63.3334 20.5435 62.4554 22.6631 60.8926 24.2259C59.3298 25.7887 57.2102 26.6667 55.0001 26.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Подарунки на день народження</h3>
        </div>

        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M30 50L50 30" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M31.6667 33.3333C32.5871 33.3333 33.3333 32.5871 33.3333 31.6667C33.3333 30.7462 32.5871 30 31.6667 30C30.7462 30 30 30.7462 30 31.6667C30 32.5871 30.7462 33.3333 31.6667 33.3333Z" fill="#1A7B9E" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M48.3334 49.9998C49.2539 49.9998 50.0001 49.2536 50.0001 48.3332C50.0001 47.4127 49.2539 46.6665 48.3334 46.6665C47.4129 46.6665 46.6667 47.4127 46.6667 48.3332C46.6667 49.2536 47.4129 49.9998 48.3334 49.9998Z" fill="#1A7B9E" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M16.6667 24.0001C16.6667 22.0552 17.4394 20.1899 18.8146 18.8146C20.1899 17.4394 22.0552 16.6667 24.0001 16.6667H27.3334C29.2697 16.6656 31.127 15.8987 32.5001 14.5334L34.8334 12.2001C35.5149 11.5147 36.3251 10.9709 37.2175 10.5998C38.11 10.2286 39.0669 10.0376 40.0334 10.0376C40.9999 10.0376 41.9569 10.2286 42.8493 10.5998C43.7417 10.9709 44.5519 11.5147 45.2334 12.2001L47.5667 14.5334C48.9398 15.8987 50.7971 16.6656 52.7334 16.6667H56.0667C58.0117 16.6667 59.8769 17.4394 61.2522 18.8146C62.6275 20.1899 63.4001 22.0552 63.4001 24.0001V27.3334C63.4012 29.2697 64.1681 31.127 65.5334 32.5001L67.8667 34.8334C68.5521 35.5149 69.0959 36.3251 69.467 37.2175C69.8382 38.11 70.0292 39.0669 70.0292 40.0334C70.0292 40.9999 69.8382 41.9569 69.467 42.8493C69.0959 43.7417 68.5521 44.5519 67.8667 45.2334L65.5334 47.5667C64.1681 48.9398 63.4012 50.7971 63.4001 52.7334V56.0667C63.4001 58.0117 62.6275 59.8769 61.2522 61.2522C59.8769 62.6275 58.0117 63.4001 56.0667 63.4001H52.7334C50.7971 63.4012 48.9398 64.1681 47.5667 65.5334L45.2334 67.8667C44.5519 68.5521 43.7417 69.0959 42.8493 69.467C41.9569 69.8382 40.9999 70.0292 40.0334 70.0292C39.0669 70.0292 38.11 69.8382 37.2175 69.467C36.3251 69.0959 35.5149 68.5521 34.8334 67.8667L32.5001 65.5334C31.127 64.1681 29.2697 63.4012 27.3334 63.4001H24.0001C22.0552 63.4001 20.1899 62.6275 18.8146 61.2522C17.4394 59.8769 16.6667 58.0117 16.6667 56.0667V52.7334C16.6656 50.7971 15.8987 48.9398 14.5334 47.5667L12.2001 45.2334C11.5147 44.5519 10.9709 43.7417 10.5998 42.8493C10.2286 41.9569 10.0376 40.9999 10.0376 40.0334C10.0376 39.0669 10.2286 38.11 10.5998 37.2175C10.9709 36.3251 11.5147 35.5149 12.2001 34.8334L14.5334 32.5001C15.8987 31.127 16.6656 29.2697 16.6667 27.3334V24.0001Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Ранній доступ до акцій</h3>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Medical Journal -->
<section class="medical-journal">
  <div class="container">
    <div class="section-header">
      <h2>Блог</h2>
      <NuxtLink to="/catalog-journal" class="view-all">Переглянути більше</NuxtLink>
    </div>
    <div v-if="latestPosts.length > 0" class="articles-grid">
      <div v-for="post in latestPosts" :key="post.id" class="article-card">
        <NuxtLink :to="`/journal/${post.slug}`" class="text-decoration-none text-dark">
          <div class="article-image">
            <span v-if="post.category" class="tag">{{ post.category }}</span>
            <img :src="postImg(post)" :alt="post.title" loading="lazy" decoding="async">
          </div>
          <div class="article-info">
            <span class="date">{{ post.formatted_date }}</span>
            <h3>{{ post.title }}</h3>
            <span class="read-more">Детальніше <span v-html="ARROW_RIGHT"></span></span>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</section>

<!-- Consultation + FAQ -->
<section class="consultation-section">
  <div class="container">
    <div class="consultation-grid">
      <div class="consultation-form">
        <h2>Запишіться на персональну консультацію</h2>
        <p>Наш фахівець допоможе підібрати ідеальні засоби Pelovit саме для ваших потреб.</p>
        <LeadForm source="home" />
      </div>
      <div v-if="faqs.length" class="container faq-container">
        <h2 class="mb-4">Часті запитання</h2>
        <div class="accordion" id="faqAccordion">
          <div v-for="(faq, i) in faqs" :key="faq.id" class="accordion-item">
            <h3 class="accordion-header">
              <button
                class="accordion-button"
                :class="{ collapsed: i !== 0 }"
                type="button"
                data-bs-toggle="collapse"
                :data-bs-target="`#home-faq-${faq.id}`"
                :aria-expanded="i === 0"
                :aria-controls="`home-faq-${faq.id}`"
              >{{ faq.question }}</button>
            </h3>
            <div
              :id="`home-faq-${faq.id}`"
              class="accordion-collapse collapse"
              :class="{ show: i === 0 }"
              data-bs-parent="#faqAccordion"
            >
              <div class="accordion-body">{{ faq.answer }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</template>
