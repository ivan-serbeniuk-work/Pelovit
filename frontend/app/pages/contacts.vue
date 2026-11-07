<script setup lang="ts">
useSeoPage({
  pageKey: 'contacts',
  fallbackTitle: 'Контакти — PELOVIT',
  fallbackDescription: 'Контакти PELOVIT. Адреса: вул. Успенська 59, Одеса. Телефон: +38 (063) 309-03-03.',
  canonicalPath: '/contacts',
})

const config = useRuntimeConfig()
const { assetUrl } = useAsset()
// Крім самих контактів (налаштування) на сторінці є блок «Наша команда».
useAdminEditTargets(() => ['settings', 'team'])

const { data } = await useFetch<any>(`${config.public.apiBase}/contacts`)
const team = computed(() => data.value?.team ?? [])

const { data: settings } = await useSettings()

// LocalBusiness: адреса й телефон у Google як картка організації.
// Адреса поки зашита в розмітці сторінки — тримаємо тут те саме значення,
// щоб розмітка не розходилася з тим, що бачить людина.
useJsonLd(() => ({
  '@context': 'https://schema.org',
  '@graph': [
    compact({
      '@type': 'LocalBusiness',
      '@id': `${config.public.siteUrl}/contacts#localbusiness`,
      name: 'PELOVIT',
      url: `${config.public.siteUrl}/contacts`,
      image: `${config.public.siteUrl}/og-default.jpg`,
      telephone: settings.value?.phone || '+38 (063) 309-03-03',
      email: settings.value?.email || undefined,
      address: {
        '@type': 'PostalAddress',
        streetAddress: 'вул. Успенська 59 / Пушкінська',
        addressLocality: 'Одеса',
        addressCountry: 'UA',
      },
      sameAs: [
        settings.value?.instagram_url,
        settings.value?.facebook_url,
        settings.value?.youtube_url,
      ].filter(Boolean),
    }),
    breadcrumbList([
      { name: 'Головна', path: '/' },
      { name: 'Контакти', path: '/contacts' },
    ], config.public.siteUrl),
  ],
}))
</script>

<template>
<section class="contact-section py-5">
  <div class="container">
    <h1 class="h2 mb-5">Контакти</h1>
    <div class="row g-5">
      <div class="col-lg-5">
        <div class="contact-info">
          <div class="contact-item">
            <div class="contact-icon"><AppIcon name="phone" /></div>
            <div>
              <div class="contact-label">Телефон</div>
              <a href="tel:+380633090303" style="color:inherit;text-decoration:none;font-weight:500;">+38 (063) 309-03-03</a>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><AppIcon name="envelope" /></div>
            <div>
              <div class="contact-label">Email</div>
              <a href="mailto:aksimed@ukr.net" style="color:inherit;text-decoration:none;font-weight:500;">aksimed@ukr.net</a>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><AppIcon name="map-marker" /></div>
            <div>
              <div class="contact-label">Адреса</div>
              <span style="font-weight:500;">вул. Успенська 59 / Пушкінська, Одеса</span>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><AppIcon name="clock" /></div>
            <div>
              <div class="contact-label">Графік роботи</div>
              <span style="font-weight:500;">пн–пт, 10:00–18:00</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="consultation-form">
          <h2>Виникли питання?</h2>
          <p>Наш фахівець допоможе підібрати ідеальні засоби Pelovit.</p>
          <LeadForm source="contacts" />
        </div>
      </div>
    </div>
    <div class="mt-5 map_section">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d3279.8955094662983!2d30.722343563286362!3d46.49097724401816!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sua!4v1778526474245!5m2!1sen!2sua"
        width="100%" height="419" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
    <div v-if="team.length > 0" class="mt-5">
      <h3 class="fw-bold mb-4">Наша команда</h3>
      <div class="row g-4">
        <div v-for="member in team" :key="member.id" class="col-lg-3 col-md-4 col-6">
          <div class="text-center">
            <img
              :src="assetUrl(member.image)"
              :alt="member.name"
              class="rounded-circle mb-3 object-fit-cover"
              style="width:120px;height:120px;"
              loading="lazy"
              decoding="async"
            >
            <h6 class="fw-semibold mb-1">{{ member.name }}</h6>
            <p class="text-muted small mb-1">{{ member.position }}</p>
            <a v-if="member.phone" :href="`tel:${member.phone}`" class="small text-decoration-none" style="color:inherit;">{{ member.phone }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</template>
