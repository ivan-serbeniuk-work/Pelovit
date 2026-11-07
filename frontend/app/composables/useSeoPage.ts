interface SeoPageOptions {
  pageKey: string
  fallbackTitle: string
  fallbackDescription: string
  fallbackOgImage?: string
  /** Рядок або геттер — для сторінок, canonical яких залежить від параметрів. */
  canonicalPath: string | (() => string)
  ogType?: string
  /**
   * Перебиває title/description, коли зміст сторінки залежить від параметрів
   * (каталог у розрізі категорії). Повертає null — беремо налаштування.
   */
  override?: () => { title?: string, description?: string } | null
  /** Вміст meta robots, напр. 'noindex, follow'. null — тег не виводимо. */
  robots?: () => string | null
}

/** Картинка для шарингу за замовчуванням, коли нічого не задано в налаштуваннях. */
const DEFAULT_OG_IMAGE = '/og-default.jpg'

export const useSeoPage = (opts: SeoPageOptions) => {
  const { siteUrl } = useRuntimeConfig().public
  const { data: settings } = useSettings()

  const overridden = computed(() => opts.override?.() ?? null)

  const title = computed(() =>
    overridden.value?.title
    || settings.value?.[`seo_${opts.pageKey}_title`]
    || opts.fallbackTitle,
  )
  const description = computed(() =>
    overridden.value?.description
    || settings.value?.[`seo_${opts.pageKey}_description`]
    || opts.fallbackDescription,
  )

  // Абсолютний URL обов'язковий: відносний шлях у og:image соцмережі не беруть.
  const ogImage = computed(() => {
    const raw = settings.value?.[`seo_${opts.pageKey}_og_image`]
      || settings.value?.default_og_image
      || opts.fallbackOgImage
      || DEFAULT_OG_IMAGE

    return raw.startsWith('http') ? raw : `${siteUrl}${raw.startsWith('/') ? '' : '/'}${raw}`
  })

  const canonical = computed(() => {
    const path = typeof opts.canonicalPath === 'function' ? opts.canonicalPath() : opts.canonicalPath

    return `${siteUrl}${path}`
  })

  const robots = computed(() => opts.robots?.() ?? null)

  useHead({
    title,
    link: computed(() => [{ rel: 'canonical', href: canonical.value }]),
    meta: computed(() => [
      { name: 'description', content: description.value },
      ...(robots.value ? [{ name: 'robots', content: robots.value }] : []),
      { property: 'og:url', content: canonical.value },
      { property: 'og:title', content: title.value },
      { property: 'og:description', content: description.value },
      { property: 'og:image', content: ogImage.value },
      { property: 'og:type', content: opts.ogType ?? 'website' },
      // twitter:card = summary_large_image заданий глобально; без twitter:image
      // Twitter підхоплює og:image, але явний тег надійніший.
      { name: 'twitter:image', content: ogImage.value },
      { name: 'twitter:title', content: title.value },
      { name: 'twitter:description', content: description.value },
    ]),
  })
}
