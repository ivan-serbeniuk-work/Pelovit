<script setup lang="ts">
useSeoPage({
  pageKey: 'opt',
  fallbackTitle: 'Оптові закупівлі — PELOVIT',
  fallbackDescription: 'Натуральна косметика та засоби оздоровлення напряму від виробника — без посередників і з гнучкими умовами співпраці.',
  canonicalPath: '/opt',
})

const config = useRuntimeConfig()
const { assetUrl } = useAsset()
const { data: settings } = await useSettings()

// Менеджери напрямків — ті самі, що на сторінці контактів (ресурс «Команда» в адмінці).
useAdminEditTargets(() => ['settings'])

const { data: contacts } = await useFetch<any>(`${config.public.apiBase}/contacts`)
const team = computed(() => contacts.value?.team ?? [])

const docTab = ref<'terms' | 'docs' | 'training'>('terms')

// Умови співпраці. Редагується тут, поки для них немає ресурсу в адмінці.
const terms = [
  { title: 'Асортимент', rows: ['доглядова косметика', 'професійна косметика', 'парфуми', 'лікувально-профілактична продукція'] },
  { title: 'Умови оплати', rows: ['Викуп продукції з можливістю обміну, що не продається', '<strong>Дроп</strong> — оплата за фактом'], highlight: true },
  { title: 'Націнка', rows: ['дроп — 30 відсотків', 'представники — 40-50 залежно від обсягів', 'салони — 30-40 відсотків залежно від обсягів продажів'] },
  { title: 'Цінова політика', rows: ['Попереджаємо про підвищення цін за 2 тижні. Ціна продажу не може бути нижчою, ніж роздрібний прайс на сайті виробника.'] },
  { title: 'Демпінг', rows: ['Ми не дозволяємо встановлювати ціни нижчі за наші, вищі — так, нижчі — ні. Акції не враховуються.'] },
  { title: 'Навчання персоналу', rows: ['Навчання: обов\'язково за нашими стандартами онлайн чи офлайн.', 'Період навчання за 7 днів до вступу товару, не пізніше. Обов\'язково під час навчання у всіх мають бути каталоги, прайс-листи та пробники продукції.'] },
  { title: 'Тестери', rows: ['Обов\'язково тестери на полиці для клієнтів в офлайн магазинах або салоні — обов\'язково та пробники.', '10% від вартості замовлення — пробники', '10 тис. замовлення — 1 тис. на пробники (1 пробник — 50 грн)'] },
]

const VISIBLE_TERMS = 4
const showAllTerms = ref(false)
const visibleTerms = computed(() => showAllTerms.value ? terms : terms.slice(0, VISIBLE_TERMS))
</script>

<template>
<section class="py-5 text-white loyalty-section">
  <div class="container">
    <div class="loyalty-content">
      <div class="loyalty-text">
        <h1>Оптові закупівлі Pelovit</h1>
        <p>Натуральна косметика та засоби оздоровлення напряму від виробника — без посередників і з гнучкими умовами співпраці.</p>
        <a href="#opt-form" class="btn-register rad-16">Залишити заявку</a>
      </div>
      <div class="loyalty-benefits">
        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
              <path d="M23.3333 36.6667V63.3333C23.3333 64.2174 22.9821 65.0652 22.357 65.6904C21.7319 66.3155 20.8841 66.6667 20 66.6667H13.3333C12.4493 66.6667 11.6014 66.3155 10.9763 65.6904C10.3512 65.0652 10 64.2174 10 63.3333V40C10 39.116 10.3512 38.2681 10.9763 37.643C11.6014 37.0179 12.4493 36.6667 13.3333 36.6667H23.3333ZM23.3333 36.6667C26.8696 36.6667 30.2609 35.2619 32.7614 32.7614C35.2619 30.261 36.6667 26.8696 36.6667 23.3333V20C36.6667 18.2319 37.3691 16.5362 38.6193 15.286C39.8695 14.0357 41.5652 13.3333 43.3333 13.3333C45.1014 13.3333 46.7971 14.0357 48.0474 15.286C49.2976 16.5362 50 18.2319 50 20V36.6667H60C61.7681 36.6667 63.4638 37.3691 64.7141 38.6193C65.9643 39.8695 66.6667 41.5652 66.6667 43.3333L63.3333 60C62.854 62.0449 61.9446 63.8008 60.7422 65.0032C59.5398 66.2056 58.1095 66.7894 56.6667 66.6667H33.3333C30.6812 66.6667 28.1376 65.6131 26.2623 63.7377C24.3869 61.8624 23.3333 59.3188 23.3333 56.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="opt_head">Вигідні умови закупівель</h3>
        </div>
        <div class="benefit-card blue">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
              <path d="M16.668 43.3333C16.668 41.5652 17.3703 39.8695 18.6206 38.6193C19.8708 37.3691 21.5665 36.6667 23.3346 36.6667C25.1027 36.6667 26.7984 37.3691 28.0487 38.6193C29.2989 39.8695 30.0013 41.5652 30.0013 43.3333V50C30.0013 51.7681 29.2989 53.4638 28.0487 54.7141C26.7984 55.9643 25.1027 56.6667 23.3346 56.6667C21.5665 56.6667 19.8708 55.9643 18.6206 54.7141C17.3703 53.4638 16.668 51.7681 16.668 50V43.3333Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M50 43.3333C50 41.5652 50.7024 39.8695 51.9526 38.6193C53.2029 37.3691 54.8986 36.6667 56.6667 36.6667C58.4348 36.6667 60.1305 37.3691 61.3807 38.6193C62.631 39.8695 63.3333 41.5652 63.3333 43.3333V50C63.3333 51.7681 62.631 53.4638 61.3807 54.7141C60.1305 55.9643 58.4348 56.6667 56.6667 56.6667C54.8986 56.6667 53.2029 55.9643 51.9526 54.7141C50.7024 53.4638 50 51.7681 50 50V43.3333Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M16.668 46.6667V40C16.668 33.8116 19.1263 27.8767 23.5022 23.5008C27.878 19.125 33.8129 16.6667 40.0013 16.6667C46.1897 16.6667 52.1246 19.125 56.5005 23.5008C60.8763 27.8767 63.3346 33.8116 63.3346 40V46.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M63.3333 56.6667V60C63.3333 62.6522 62.281 65.1957 60.4056 67.0711C58.5303 68.9464 55.9855 70 53.3333 70H40" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="opt_head">Професійна підтримка</h3>
        </div>
        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
              <path d="M10 30C10 29.116 10.3512 28.2681 10.9763 27.643C11.6014 27.0179 12.4493 26.6667 13.3333 26.6667H66.6667C67.5507 26.6667 68.3986 27.0179 69.0237 27.643C69.6488 28.2681 70 29.116 70 30V36.6667C70 37.5507 69.6488 38.3986 69.0237 39.0237C68.3986 39.6488 67.5507 40 66.6667 40H13.3333C12.4493 40 11.6014 39.6488 10.9763 39.0237C10.3512 38.3986 10 37.5507 10 36.6667V30Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 26.6667V70" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M63.3346 40V63.3333C63.3346 65.1014 62.6323 66.7971 61.382 68.0474C60.1318 69.2976 58.4361 70 56.668 70H23.3346C21.5665 70 19.8708 69.2976 18.6206 68.0474C17.3703 66.7971 16.668 65.1014 16.668 63.3333V40" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M25.0013 26.6667C22.7912 26.6667 20.6715 25.7887 19.1087 24.2259C17.5459 22.6631 16.668 20.5435 16.668 18.3333C16.668 16.1232 17.5459 14.0036 19.1087 12.4408C20.6715 10.878 22.7912 9.99999 25.0013 9.99999C28.2169 9.94397 31.3681 11.5042 34.0438 14.4772C36.7195 17.4502 38.7956 21.698 40.0013 26.6667C41.2071 21.698 43.2831 17.4502 45.9589 14.4772C48.6346 11.5042 51.7857 9.94397 55.0013 9.99999C57.2114 9.99999 59.3311 10.878 60.8939 12.4408C62.4567 14.0036 63.3346 16.1232 63.3346 18.3333C63.3346 20.5435 62.4567 22.6631 60.8939 24.2259C59.3311 25.7887 57.2114 26.6667 55.0013 26.6667" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="opt_head">Бонуси та привілеї</h3>
        </div>
        <div class="benefit-card white">
          <div class="icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
              <path d="M60 13.3333C62.6522 13.3333 65.1957 14.3869 67.0711 16.2622C68.9464 18.1376 70 20.6811 70 23.3333V50C70 52.6521 68.9464 55.1957 67.0711 57.0711C65.1957 58.9464 62.6522 60 60 60H43.3333L26.6667 70V60H20C17.3478 60 14.8043 58.9464 12.9289 57.0711C11.0536 55.1957 10 52.6521 10 50V23.3333C10 20.6811 11.0536 18.1376 12.9289 16.2622C14.8043 14.3869 17.3478 13.3333 20 13.3333H60Z" stroke="#1A7B9E" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="opt_head">Підтримка клієнтів</h3>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="suite_for_section container grid-container">
  <h2 class="header">Кому підійде:</h2>
  <div class="row g-3">
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M53.3346 41.6666L36.668 31.6666L53.3346 21.6666L70.0013 31.6666V50L53.3346 60V41.6666Z" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M36.668 31.6666V50L53.3346 60" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M53.332 41.8167L69.9987 31.7167" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M23.3346 30H6.66797" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M23.332 40H13.332" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M23.3333 50H20" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Дропшипінг</h5>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M10 70H70" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M50 26.6667C50 29.3188 51.0536 31.8624 52.9289 33.7377C54.8043 35.6131 57.3478 36.6667 60 36.6667C62.6522 36.6667 65.1957 35.6131 67.0711 33.7377C68.9464 31.8624 70 29.3188 70 26.6667V23.3333L63.3333 10H16.6667L10 23.3333V26.6667C10 29.3188 11.0536 31.8624 12.9289 33.7377C14.8043 35.6131 17.3478 36.6667 20 36.6667C22.6522 36.6667 25.1957 35.6131 27.0711 33.7377C28.9464 31.8624 30 29.3188 30 26.6667M10 23.3333H70M30 26.6667V23.3333M30 26.6667C30 29.3188 31.0536 31.8624 32.9289 33.7377C34.8043 35.6131 37.3478 36.6667 40 36.6667C42.6522 36.6667 45.1957 35.6131 47.0711 33.7377C48.9464 31.8624 50 29.3188 50 26.6667M50 26.6667V23.3333" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M16.668 70V36.1666" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M63.332 70V36.1666" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M30 70V56.6667C30 54.8986 30.7024 53.2029 31.9526 51.9526C33.2029 50.7024 34.8986 50 36.6667 50H43.3333C45.1014 50 46.7971 50.7024 48.0474 51.9526C49.2976 53.2029 50 54.8986 50 56.6667V70" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Магазини косметики<br>(онлайн/офлайн)</h5>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M39.9987 10L66.6654 25V55L39.9987 70L13.332 55V25L39.9987 10Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M40 40L66.6667 25" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M40 40V70" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M39.9987 40L13.332 25" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M53.3346 17.5L26.668 32.5" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Опт покупки</h5>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M26.668 23.3333C26.668 26.8696 28.0727 30.2609 30.5732 32.7614C33.0737 35.2619 36.4651 36.6667 40.0013 36.6667C43.5375 36.6667 46.9289 35.2619 49.4294 32.7614C51.9299 30.2609 53.3346 26.8696 53.3346 23.3333C53.3346 19.7971 51.9299 16.4057 49.4294 13.9052C46.9289 11.4048 43.5375 10 40.0013 10C36.4651 10 33.0737 11.4048 30.5732 13.9052C28.0727 16.4057 26.668 19.7971 26.668 23.3333Z" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M20 70V63.3333C20 59.7971 21.4048 56.4057 23.9052 53.9052C26.4057 51.4048 29.7971 50 33.3333 50H46.6667C50.2029 50 53.5943 51.4048 56.0948 53.9052C58.5952 56.4057 60 59.7971 60 63.3333V70" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Дистриб'ютор</h5>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M36.668 56.6666C37.7194 57.1255 38.8541 57.3623 40.0013 57.3623C41.1485 57.3623 42.2833 57.1255 43.3346 56.6666" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M40.0002 10C24.4535 10 15.3469 17.77 13.7935 28.65C12.2351 38.6993 14.6239 48.962 20.4602 57.29C23.2691 61.3961 26.9002 64.874 31.1235 67.5033C36.6769 70.8367 43.3369 70.8367 48.8902 67.5033C53.1135 64.874 56.7446 61.3961 59.5535 57.29C65.3661 48.9514 67.7528 38.6983 66.2202 28.65C64.6669 17.7667 55.5602 10 40.0135 10H40.0002Z" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M26.668 36.6666L33.3346 43.3333" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M53.3346 36.6666L46.668 43.3333" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Б'юті простори + школи</h5>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="card p-4 text-center">
          <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
          <path d="M16.668 23.3333C16.668 26.8696 18.0727 30.2609 20.5732 32.7614C23.0737 35.2619 26.4651 36.6667 30.0013 36.6667C33.5375 36.6667 36.9289 35.2619 39.4294 32.7614C41.9299 30.2609 43.3346 26.8696 43.3346 23.3333C43.3346 19.7971 41.9299 16.4057 39.4294 13.9052C36.9289 11.4048 33.5375 10 30.0013 10C26.4651 10 23.0737 11.4048 20.5732 13.9052C18.0727 16.4057 16.668 19.7971 16.668 23.3333Z" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10 70V63.3333C10 59.7971 11.4048 56.4057 13.9052 53.9052C16.4057 51.4048 19.7971 50 23.3333 50H36.6667C40.2029 50 43.5943 51.4048 46.0948 53.9052C48.5952 56.4057 50 59.7971 50 63.3333V70" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M53.332 10.4333C56.2001 11.1677 58.7422 12.8357 60.5575 15.1744C62.3728 17.5131 63.3582 20.3894 63.3582 23.35C63.3582 26.3106 62.3728 29.187 60.5575 31.5256C58.7422 33.8643 56.2001 35.5323 53.332 36.2667" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M70 70V63.3333C69.9831 60.3905 68.993 57.536 67.1842 55.2147C65.3754 52.8933 62.8494 51.2357 60 50.5" stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h5 class="fw-medium">Дистриб'ютор-<br>представник у областях</h5>
        </div>
      </div>
  </div>
</section>

<section v-if="team.length" class="our_contacts_section">
  <div class="container">
    <div class="row g-4">
      <div v-for="member in team" :key="member.id" class="col-lg-2-custom col-md-4 col-sm-6 col-12">
        <div class="contact-card h-100">
          <img
            :src="assetUrl(member.image)"
            :alt="`${member.position}${member.name ? ' — ' + member.name : ''}`"
            loading="lazy"
            decoding="async"
          >
          <div class="contact-info">
            <h5>{{ member.name }}</h5>
            <p class="position">{{ member.position }}</p>
            <a
              v-if="member.phone"
              :href="`tel:${member.phone.replace(/\D/g, '')}`"
              class="phone text-decoration-none"
            >☎ {{ member.phone }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="drop_shipping_section">
  <div class="container">
    <div class="hero-content">
      <h2>Опановуй дропшипінг з нашою платформою!</h2>
      <p>
        Гейміфіковане навчання з короткими відео, тестами та MindMap.
        Пройди курс і отримай набір тестерів продукції на 5000 грн.
        Місця обмежені — лише 10 учасників!
      </p>
      <a href="#opt-form" class="btn btn-apply">Подати заявку</a>
    </div>
  </div>
</section>

<section class="doc_learn_cond_section">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 mb-4">
        <div class="sidebar">
          <button
            class="sidebar-btn"
            :class="{ active: docTab === 'terms' }"
            @click="docTab = 'terms'"
          ><strong>Умови співпраці</strong></button>
          <button
            class="sidebar-btn"
            :class="{ active: docTab === 'docs' }"
            @click="docTab = 'docs'"
          >Документи</button>
          <button
            class="sidebar-btn"
            :class="{ active: docTab === 'training' }"
            @click="docTab = 'training'"
          >Навчання</button>
        </div>
      </div>

      <div class="col-lg-9">
        <div v-if="docTab === 'terms'">
          <div class="table-container">
            <table class="table">
              <tbody>
                <tr v-for="row in visibleTerms" :key="row.title" :class="{ highlight: row.highlight }">
                  <th>{{ row.title }}</th>
                  <td>
                    <p v-for="(line, i) in row.rows" :key="i" class="mb-2" v-html="line"></p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="terms.length > VISIBLE_TERMS" class="view-more">
            <button class="view-all mx-auto btn" @click="showAllTerms = !showAllTerms">
              {{ showAllTerms ? 'Згорнути' : 'Переглянути більше' }}
            </button>
          </div>
        </div>

        <p v-else-if="docTab === 'docs'" class="text-muted py-4">
          Пакет документів для партнерів надсилає менеджер після заявки — залиште контакти нижче.
        </p>

        <p v-else class="text-muted py-4">
          Навчання проходить онлайн або офлайн за стандартами виробника, за 7 днів до першого
          постачання. Деталі — у менеджера напрямку.
        </p>
      </div>
    </div>
  </div>
</section>

<div class="kontractne_vyrobnyctvo_page">
  <section id="opt-form" class="develop_cons_section">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6 left-content">
          <h2>Потрібна додаткова інформація?</h2>
          <p class="lead" style="font-size: 1.2rem; line-height: 1.5;">
            Залиште свої контакти, і ми надамо консультацію з усіх питань співпраці.
          </p>
        </div>

        <div class="col-lg-6">
          <div class="consultation-form">
            <h4>Зв'язатись з нами</h4>
            <LeadForm source="opt" :show-company="true" :contact-methods="['call','telegram','viber','whatsapp']" />
            <div class="social-links mt-3">
              <p>Ви можете написати нам самі:</p>
              <div class="social-icons">
                <a
                  v-if="settings?.instagram_url"
                  :href="settings.instagram_url"
                  target="_blank"
                  rel="noopener"
                  class="social-link"
                >Instagram</a>
                <a
                  v-if="settings?.telegram_url"
                  :href="settings.telegram_url"
                  target="_blank"
                  rel="noopener"
                  class="social-link"
                >Telegram</a>
                <a
                  v-if="settings?.viber_url"
                  :href="settings.viber_url"
                  target="_blank"
                  rel="noopener"
                  class="social-link"
                >Viber</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</template>
