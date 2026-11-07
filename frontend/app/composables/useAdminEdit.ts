export interface AdminEditLink {
  label: string
  url: string
}

interface Declared {
  /** Шлях, для якого цілі актуальні. */
  path: string
  targets: string[]
}

const STATE_KEY = 'admin-edit-targets'
const EMPTY: Declared = { path: '', targets: [] }

/**
 * Сторінка оголошує, що в ній можна редагувати: `product:18` — форма запису,
 * `faq` — розділ цілком. Док у лейауті це читає й питає в API посилання.
 *
 * Цілі зберігаємо разом зі шляхом: під час переходу новий компонент
 * монтується раніше, ніж розмонтовується старий, тож «очистити на unmount»
 * ловило б гонку і гасило б кнопку на щойно відкритій сторінці.
 */
export function useAdminEditTargets(getter: () => (string | null | undefined | false)[]): void {
  const route = useRoute()
  const declared = useState<Declared>(STATE_KEY, () => EMPTY)

  watchEffect(() => {
    declared.value = {
      path: route.path,
      targets: getter().filter((t): t is string => typeof t === 'string' && t.length > 0),
    }
  })
}

/**
 * Посилання в адмінку для поточної сторінки — лише для того, хто вже
 * залогінений у MoonShine.
 *
 * Запит іде тільки з браузера і з кукою сесії: на сервері (SSR) її немає, та
 * й кешувати таку відповідь не можна — для звичайного покупця вона порожня.
 * У застосунку (Capacitor) адмінка не потрібна, тому там не питаємо взагалі.
 */
export function useAdminEditLinks() {
  const config = useRuntimeConfig()
  const route = useRoute()
  const declared = useState<Declared>(STATE_KEY, () => EMPTY)
  const links = ref<AdminEditLink[]>([])

  const targets = computed(() =>
    declared.value.path === route.path ? declared.value.targets : [],
  )

  async function load() {
    if (config.public.isApp || targets.value.length === 0) {
      links.value = []
      return
    }

    try {
      const res = await $fetch<{ authenticated: boolean, links: AdminEditLink[] }>(
        `${config.public.apiBase}/admin/edit-links`,
        { credentials: 'include', query: { targets: targets.value.join(',') } },
      )
      links.value = res?.links ?? []
    } catch {
      // Гість отримує 401/419 або CORS-помилку — просто не показуємо док.
      links.value = []
    }
  }

  onMounted(load)
  watch(targets, load)

  return { links }
}
