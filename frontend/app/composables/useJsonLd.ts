/**
 * Розмітка schema.org. Кожен виклик додає свій <script type="application/ld+json">;
 * Google спокійно читає кілька блоків на сторінці, тож ділимо за змістом:
 * Organization і WebSite — у лейауті, Product/Article/BreadcrumbList — на сторінках.
 *
 * build() повертає null, поки дані ще не приїхали, — тоді блок не рендериться
 * взагалі, щоб у розмітку не потрапив об'єкт з порожніми полями.
 */
export function useJsonLd(build: () => Record<string, unknown> | null): void {
  useHead({
    script: computed(() => {
      const data = build()

      return data ? [{ type: 'application/ld+json', innerHTML: JSON.stringify(data) }] : []
    }),
  })
}

export interface Crumb {
  name: string
  /** Шлях від кореня, напр. `/catalog`. Останній елемент — поточна сторінка. */
  path: string
}

/** BreadcrumbList: у сніпеті замість голого URL з'являються крихти. */
export function breadcrumbList(crumbs: Crumb[], siteUrl: string): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: crumbs.map((c, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: c.name,
      item: `${siteUrl}${c.path}`,
    })),
  }
}

/** Прибирає порожні поля — у розмітці вони лише шум і привід для попереджень. */
export function compact<T extends Record<string, unknown>>(obj: T): T {
  return Object.fromEntries(
    Object.entries(obj).filter(([, v]) => v !== undefined && v !== null && v !== ''),
  ) as T
}
