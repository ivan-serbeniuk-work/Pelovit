/**
 * 301 зі старого формату категорії на новий: /catalog?category=slug →
 * /catalog/slug. Решта параметрів (сортування, бренд, ціна, пошук)
 * переноситься, сторінку скидаємо — нумерація в новому розрізі своя.
 *
 * Редірект серверний, а не в middleware сторінки: так його бачать і
 * пошуковики, і зовнішні посилання, не витрачаючи рендер Vue.
 */
export default defineEventHandler((event) => {
  const url = getRequestURL(event)

  if (url.pathname !== '/catalog') return

  const category = url.searchParams.get('category')

  if (!category) return

  // Пропускаємо все, що не схоже на слаг: інакше редіректили б на сміттєві
  // адреси за будь-яким вмістом параметра.
  if (!/^[a-z0-9-]{1,80}$/.test(category)) return

  const rest = new URLSearchParams(url.searchParams)
  rest.delete('category')
  rest.delete('page')

  const query = rest.toString()

  return sendRedirect(event, `/catalog/${category}${query ? `?${query}` : ''}`, 301)
})
