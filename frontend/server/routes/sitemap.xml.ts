import { masterCategories } from '~/data/masterCategories'

/** В XML амперсанд у query-параметрах обов'язково екранується. */
const xmlEscape = (v: string) =>
  v.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

export default defineEventHandler(async () => {
  const apiBase = process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api'
  const siteUrl = process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000'

  const staticPages = [
    { loc: '/',                        priority: '1.0', changefreq: 'weekly'  },
    { loc: '/catalog',                 priority: '0.9', changefreq: 'daily'   },
    { loc: '/journal',                 priority: '0.8', changefreq: 'weekly'  },
    { loc: '/promotions',              priority: '0.7', changefreq: 'weekly'  },
    { loc: '/contacts',                priority: '0.6', changefreq: 'monthly' },
    { loc: '/about',                   priority: '0.5', changefreq: 'monthly' },
    { loc: '/opt',                     priority: '0.6', changefreq: 'monthly' },
    { loc: '/masters',                 priority: '0.6', changefreq: 'monthly' },
    { loc: '/kontractne-vyrobnyctvo',  priority: '0.5', changefreq: 'monthly' },
    { loc: '/obmin-ta-povernennya',    priority: '0.3', changefreq: 'yearly'  },
    // /catalog-journal навмисно відсутня — це 301-редирект на /journal
  ]

  const urls: { loc: string; lastmod?: string; priority: string; changefreq: string }[] = [...staticPages]

  // Сторінки «Майстрам → категорія». Раніше в карті лежала гола
  // /masters-category, а canonical у неї веде на конкретну процедуру —
  // карта й canonical суперечили одне одному. Тепер кладемо саме ті URL,
  // які сторінка й оголошує канонічними.
  for (const cat of masterCategories) {
    if (!cat.procedures.length) {
      urls.push({ loc: `/masters-category?category=${cat.slug}`, priority: '0.5', changefreq: 'monthly' })
      continue
    }

    for (const proc of cat.procedures) {
      urls.push({
        loc: `/masters-category?category=${cat.slug}&procedure=${proc.slug}`,
        priority: '0.5',
        changefreq: 'monthly',
      })
    }
  }

  try {
    // Products — fetch all pages
    let page = 1
    while (true) {
      const data: any = await $fetch(`${apiBase}/catalog?per_page=100&page=${page}`)

      // Категорії приїжджають разом із першою сторінкою каталогу.
      if (page === 1) {
        const walk = (list: any[]) => {
          for (const cat of list ?? []) {
            if (cat?.slug) {
              urls.push({
                loc: `/catalog/${cat.slug}`,
                priority: '0.7',
                changefreq: 'weekly',
              })
            }
            if (cat?.children?.length) walk(cat.children)
          }
        }
        walk(data?.categories ?? [])
      }

      const products: any[] = data?.products?.data ?? []
      if (!products.length) break
      for (const p of products) {
        if (p.no_index) continue
        urls.push({
          loc: `/product/${p.slug}`,
          lastmod: p.updated_at?.slice(0, 10),
          priority: '0.8',
          changefreq: 'weekly',
        })
      }
      if (!data?.products?.next_page_url) break
      page++
    }
  } catch {}

  try {
    // Journal posts — fetch all pages
    let page = 1
    while (true) {
      const data: any = await $fetch(`${apiBase}/journal?per_page=100&page=${page}`)
      const posts: any[] = data?.posts?.data ?? []
      if (!posts.length) break
      for (const p of posts) {
        if (p.no_index) continue
        urls.push({
          loc: `/journal/${p.slug}`,
          lastmod: p.published_at?.slice(0, 10),
          priority: '0.7',
          changefreq: 'monthly',
        })
      }
      if (!data?.posts?.next_page_url) break
      page++
    }
  } catch {}

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.map(u => `  <url>
    <loc>${xmlEscape(siteUrl + u.loc)}</loc>${u.lastmod ? `\n    <lastmod>${u.lastmod}</lastmod>` : ''}
    <changefreq>${u.changefreq}</changefreq>
    <priority>${u.priority}</priority>
  </url>`).join('\n')}
</urlset>`

  return new Response(xml, {
    headers: { 'Content-Type': 'application/xml; charset=utf-8' },
  })
})
