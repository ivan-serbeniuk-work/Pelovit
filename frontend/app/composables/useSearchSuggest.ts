export type SuggestProduct = {
  id: number
  name: string
  slug: string
  image: string | null
  price: string | number
  old_price: string | number | null
  discount_percent: number | null
}

export type SuggestCategory = { id: number, name: string, slug: string }

export type SuggestResults = {
  products: SuggestProduct[]
  categories: SuggestCategory[]
  total: number
}

const EMPTY: SuggestResults = { products: [], categories: [], total: 0 }

/** Коротший запит дає сміттєву видачу — не смикаємо сервер. */
const MIN_LENGTH = 2
const DEBOUNCE_MS = 250

/**
 * Підказки пошуку: дебаунс плюс захист від застарілих відповідей.
 *
 * Той самий підхід, що й у пошуку міста Нової Пошти (pages/order/index.vue),
 * але з лічильником запитів: без нього повільна відповідь на «кре»
 * перетирає свіжу на «крем», і у випадайці опиняється не те, що набрано.
 */
export function useSearchSuggest() {
  const config = useRuntimeConfig()

  const results = ref<SuggestResults>({ ...EMPTY })
  const loading = ref(false)

  let debounce: ReturnType<typeof setTimeout> | undefined
  let lastRequest = 0

  function clear() {
    clearTimeout(debounce)
    lastRequest++
    results.value = { ...EMPTY }
    loading.value = false
  }

  function search(raw: string) {
    const q = raw.trim()

    clearTimeout(debounce)

    if (q.length < MIN_LENGTH) {
      clear()
      return
    }

    loading.value = true

    debounce = setTimeout(async () => {
      const requestId = ++lastRequest

      try {
        const data = await $fetch<SuggestResults>(`${config.public.apiBase}/search/suggest`, {
          query: { q },
        })

        if (requestId !== lastRequest) return

        results.value = data ?? { ...EMPTY }
      }
      catch {
        if (requestId !== lastRequest) return

        results.value = { ...EMPTY }
      }
      finally {
        if (requestId === lastRequest) loading.value = false
      }
    }, DEBOUNCE_MS)
  }

  return { results, loading, search, clear, MIN_LENGTH }
}
