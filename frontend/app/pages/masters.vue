<script setup lang="ts">
useSeoPage({
  pageKey: 'masters',
  fallbackTitle: 'Майстрам — PELOVIT',
  fallbackDescription: 'Відеопротоколи масажів з продуктами Pelovit. Безкоштовний відеопротокол та розрахунок процедури для майстрів.',
  canonicalPath: '/masters',
})

const { data: settings } = await useSettings()
</script>

<template>
<section class="hero masters-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="fw-bold mb-4">Pelovit — натуральна сила Куяльницького лиману для здоров'я та краси вдома</h1>
        <p class="lead fs-5">• Натуральні компоненти, перевірені часом</p>
        <p class="lead fs-5">• Ефект санаторію без виходу з дому</p>
        <p class="lead fs-5">• Продукти для тіла, обличчя та загального оздоровлення</p>
        <a href="#zayavka" class="btn btn-light btn-lg px-3 py-3 fw-medium rad-16">Дізнатись більше</a>
      </div>
      <div class="col-lg-5">
        <div class="row g-4">
          <div class="col-6">
            <NuxtLink :to="{ path: '/masters-category', query: { category: 'spa-masazhi' } }" class="stat-card text-decoration-none text-dark">
              <p class="mb-0 fw-medium">Спа-масажі</p>
              <i>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M16 4L18.5 10H25L20 14L22 21L16 17L10 21L12 14L7 10H13.5L16 4Z" stroke="#7B4F3A" stroke-width="1.5" fill="none"/></svg>
              </i>
            </NuxtLink>
          </div>
          <div class="col-6">
            <NuxtLink :to="{ path: '/masters-category', query: { category: 'likuvalni-masazhi' } }" class="stat-card text-decoration-none text-dark">
              <p class="mb-0 fw-medium">Лікувальні масажі</p>
              <i>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="6" y="4" width="20" height="24" rx="2" stroke="#7B4F3A" stroke-width="1.5"/><path d="M10 10H22M10 14H22M10 18H18" stroke="#7B4F3A" stroke-width="1.5" stroke-linecap="round"/><path d="M20 20L22 22L26 18" stroke="#7B4F3A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </i>
            </NuxtLink>
          </div>
          <div class="col-6">
            <NuxtLink :to="{ path: '/masters-category', query: { category: 'antytseliulitni-masazhi' } }" class="stat-card text-decoration-none text-dark">
              <p class="mb-0 fw-medium">Антицелюлітні масажі</p>
              <i>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M16 8C16 8 12 12 12 17C12 19.761 14.239 22 17 22C19.761 22 22 19.761 22 17C22 12 16 8 16 8Z" stroke="#7B4F3A" stroke-width="1.5" fill="none"/><line x1="16" y1="8" x2="16" y2="4" stroke="#7B4F3A" stroke-width="1.5" stroke-linecap="round"/></svg>
              </i>
            </NuxtLink>
          </div>
          <div class="col-6">
            <NuxtLink :to="{ path: '/masters-category', query: { category: 'aparatni-masazhi' } }" class="stat-card text-decoration-none text-dark">
              <p class="mb-0 fw-medium">Апаратні масажі</p>
              <i>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="8" y="10" width="16" height="12" rx="2" stroke="#7B4F3A" stroke-width="1.5"/><circle cx="16" cy="16" r="3" stroke="#7B4F3A" stroke-width="1.5"/><path d="M12 8V10M20 8V10M8 22L6 26M24 22L26 26" stroke="#7B4F3A" stroke-width="1.5" stroke-linecap="round"/></svg>
              </i>
            </NuxtLink>
          </div>
          <div class="col-6">
            <NuxtLink :to="{ path: '/masters-category', query: { category: 'masazhi-oblychchia' } }" class="stat-card text-decoration-none text-dark">
              <p class="mb-0 fw-medium">Масажі обличчя</p>
              <i>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="10" stroke="#7B4F3A" stroke-width="1.5" fill="none"/><path d="M12 18C12 18 14 20 16 20C18 20 20 18 20 18" stroke="#7B4F3A" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="14" r="1.5" fill="#7B4F3A"/><circle cx="20" cy="14" r="1.5" fill="#7B4F3A"/></svg>
              </i>
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="kontractne_vyrobnyctvo_page">
  <section class="develop_cons_section" id="zayavka">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6 left-content">
          <h2>Отримайте безкоштовний відеопротокол та розрахунок процедури</h2>
          <p class="lead" style="font-size: 1.2rem; line-height: 1.5;">
            Залиште свої контакти — ми надішлемо 1 відеопротокол безкоштовно, а також підготуємо калькуляцію собівартості.
          </p>
        </div>
        <div class="col-lg-6">
          <div class="consultation-form">
            <h4>Зв'язатись з нами</h4>
            <LeadForm source="masters" :contact-methods="['call','telegram','viber','whatsapp']" />
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
