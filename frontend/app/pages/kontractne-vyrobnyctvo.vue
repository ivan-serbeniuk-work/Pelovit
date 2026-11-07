<script setup lang="ts">
useSeoPage({
  pageKey: 'contract',
  fallbackTitle: 'Контрактне виробництво косметики — PELOVIT',
  fallbackDescription: 'Виготовляємо косметику під вашим брендом — від розробки формули до готової продукції. GMP ISO 22716.',
  canonicalPath: '/kontractne-vyrobnyctvo',
})

const config = useRuntimeConfig()
const { assetUrl } = useAsset()

const contactMethods = ['call', 'telegram', 'viber', 'whatsapp']

// «Працюємо для» — статичний список професій із макета: у БД такої таксономії немає.
const audiences = [
  { image: 'images/massage1.png', name: 'Масажистів та спа майстрів' },
  { image: 'images/cosmetology2.png', name: 'Косметологів' },
  { image: 'images/med3.png', name: 'Медичних та оздоровчих центрів' },
  { image: 'images/tryhologit4.png', name: 'Трихологів' },
  { image: 'images/podologiv5.png', name: 'Подологів' },
  { image: 'images/manikure6.png', name: 'Майстрів манікюру' },
  { image: 'images/brow7.png', name: 'Лашмейкерів та бровістів' },
  { image: 'images/epilation8.png', name: 'Майстрів епіляції' },
]

const steps = [
  'Ви залишаєте заявку',
  'Обговорюєте з менеджером бажані засоби',
  'Аксимед пропонує рецептуру та надає зразки',
  'Заключаємо договір',
  'Ви вносите передоплату 50%',
  'Обираємо тару',
  'Ви отримуєте 3 безкоштовні години з дизайнером на етикетку',
  'Аксимед виробляє, фасує засіб та клеїть етикетки',
  'Ви вносите остаточну оплату',
  'Аксимед відправляє ваше замовлення та сертифікати',
]

// Відгуки, кейси і FAQ живуть у БД: раніше додати новий відгук міг лише
// розробник, бо вони були захардкожені прямо тут.
// Сторінка зібрана з кейсів, відгуків, FAQ і довідників калькулятора.
useAdminEditTargets(() => [
  'brand-case', 'testimonial', 'faq', 'calculator-option', 'calculator-tier',
])

const { data: content } = await useFetch<any>(`${config.public.apiBase}/contract`)

const testimonials = computed<any[]>(() => content.value?.testimonials ?? [])
const brandCases = computed<any[]>(() => content.value?.cases ?? [])
const faqs = computed<any[]>(() => content.value?.faqs ?? [])

// Карусель показує по два відгуки на слайд.
const testimonialSlides = computed(() => {
  const slides: any[][] = []
  for (let i = 0; i < testimonials.value.length; i += 2) {
    slides.push(testimonials.value.slice(i, i + 2))
  }
  return slides
})

// FAQPage-розмітка: питання про мінімальний тираж і терміни люди
// шукають у пошуку, а не тільки на сайті.
useHead({
  script: computed(() => (faqs.value.length
    ? [{
        type: 'application/ld+json',
        innerHTML: JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'FAQPage',
          mainEntity: faqs.value.map(faq => ({
            '@type': 'Question',
            name: faq.question,
            acceptedAnswer: { '@type': 'Answer', text: faq.answer },
          })),
        }),
      }]
    : [])),
})

// --- Калькулятор ---
// Прайс живе в БД і редагується в адмінці (Калькулятор: опції / знижки за тираж),
// щоб ставки міняв менеджер, а не реліз фронтенду.
interface CalcOption { id: number, name: string, value: number, image: string | null }

const { data: calc } = await useFetch<any>(`${config.public.apiBase}/calculator`)

const productTypes = computed<CalcOption[]>(() => calc.value?.products ?? [])
const formulaTypes = computed<CalcOption[]>(() => calc.value?.formulas ?? [])
const packagings = computed<CalcOption[]>(() => calc.value?.packagings ?? [])
const labelTypes = computed<CalcOption[]>(() => calc.value?.labels ?? [])
const boxTypes = computed<CalcOption[]>(() => calc.value?.boxes ?? [])

const productId = ref<number | null>(null)
const formulaId = ref<number | null>(null)
const packagingId = ref<number | null>(null)
const labelId = ref<number | null>(null)
const boxId = ref<number | null>(null)
// Верхня межа тиражу мусить покривати відповідь «більше 1000 шт» в опитуванні,
// інакше повзунок і підсумок розійдуться.
const QTY_MIN = 10
const QTY_MAX = 2000

const volume = ref(30)
const quantity = ref(100)

watchEffect(() => {
  if (productId.value === null && productTypes.value.length) productId.value = productTypes.value[0]!.id
  if (formulaId.value === null && formulaTypes.value.length) formulaId.value = formulaTypes.value[0]!.id
  if (packagingId.value === null && packagings.value.length) packagingId.value = packagings.value[0]!.id
  if (labelId.value === null && labelTypes.value.length) labelId.value = labelTypes.value[0]!.id
  if (boxId.value === null && boxTypes.value.length) boxId.value = boxTypes.value[0]!.id
})

function pick(list: CalcOption[], id: number | null) {
  return list.find(o => o.id === id) ?? null
}

const selectedProduct = computed(() => pick(productTypes.value, productId.value))
const selectedFormula = computed(() => pick(formulaTypes.value, formulaId.value))
const selectedPackaging = computed(() => pick(packagings.value, packagingId.value))
const selectedLabel = computed(() => pick(labelTypes.value, labelId.value))
const selectedBox = computed(() => pick(boxTypes.value, boxId.value))

const hasPricing = computed(() => Boolean(selectedProduct.value && selectedFormula.value))

// Ступінь знижки — найвища з тих, поріг яких пройдено.
function tierFor(qty: number) {
  const tiers = (calc.value?.tiers ?? []) as { min_quantity: number, discount_percent: number }[]
  return tiers
    .filter(t => qty >= t.min_quantity)
    .sort((a, b) => b.min_quantity - a.min_quantity)[0] ?? null
}
const activeTier = computed(() => tierFor(quantity.value))
const discountPercent = computed(() => activeTier.value?.discount_percent ?? 0)

// Собівартість рецептури + витратні матеріали, до знижки за тираж.
const baseUnitPrice = computed(() => {
  if (!hasPricing.value) return 0
  const recipe = selectedProduct.value!.value * volume.value * selectedFormula.value!.value
  const materials = (selectedPackaging.value?.value ?? 0)
    + (selectedLabel.value?.value ?? 0)
    + (selectedBox.value?.value ?? 0)
  return recipe + materials
})

// Ціна за одиницю залежить від тиражу — знижка застосовується за порогом.
function unitPriceFor(qty: number) {
  return baseUnitPrice.value * (1 - (tierFor(qty)?.discount_percent ?? 0) / 100)
}

const unitPrice = computed(() => unitPriceFor(quantity.value))
const batchTotal = computed(() => unitPrice.value * quantity.value)

const spread = computed(() => (calc.value?.spread_percent ?? 0) / 100)
const minBatchTotal = computed(() => calc.value?.min_batch_total ?? 0)
const productionDays = computed(() => calc.value?.production_days ?? 0)

function money(value: number) {
  return new Intl.NumberFormat('uk-UA', { maximumFractionDigits: 0 }).format(Math.round(value))
}
function range(value: number) {
  return spread.value > 0
    ? `${money(value * (1 - spread.value))}–${money(value * (1 + spread.value))} ₴`
    : `${money(value)} ₴`
}

const unitPriceRange = computed(() => range(unitPrice.value))
const batchTotalRange = computed(() => range(batchTotal.value))

// Головна користь калькулятора: одразу сказати, чи взагалі проходить така партія.
const belowMinimum = computed(
  () => minBatchTotal.value > 0 && batchTotal.value > 0 && batchTotal.value < minBatchTotal.value,
)

// Клієнт бачить не точну суму, а вилку ±spread. Якщо мінімум потрапляє
// всередину вилки, стверджувати «партія менша за мінімальну» не можна —
// це межовий випадок, і формулювання має бути іншим.
const batchTotalUpper = computed(() => batchTotal.value * (1 + spread.value))
const borderlineMinimum = computed(
  () => belowMinimum.value && batchTotalUpper.value >= minBatchTotal.value,
)

// Тираж, за якого партія дотягує до мінімальної суми. Ціна за одиницю сама
// падає зі зростанням тиражу, тому просте ділення дає замалу відповідь:
// на знайденому тиражі спрацьовує знижка і сума знову провалюється під мінімум.
// Тому підтягуємо число, доки воно не перестане рости.
const requiredQuantity = computed(() => {
  if (baseUnitPrice.value <= 0 || minBatchTotal.value <= 0) return 0

  let qty = Math.ceil(minBatchTotal.value / unitPriceFor(quantity.value))

  // Кожен перехід через поріг тиражу здешевлює одиницю — ітерацій потрібно
  // не більше, ніж порогів, але обмежуємо цикл про всяк випадок.
  for (let i = 0; i < 10; i++) {
    const next = Math.ceil(minBatchTotal.value / unitPriceFor(qty))
    if (next <= qty) break
    qty = next
  }

  return qty
})

// За межами повзунка порадити «збільште тираж» вже не можна.
const requiredExceedsMax = computed(() => requiredQuantity.value > QTY_MAX)

// Те, що клієнт нарахував, їде в заявку — інакше менеджер отримує самий лише телефон.
const calculatorSummary = computed(() => {
  const lines = [
    `Продукт: ${selectedProduct.value?.name ?? '—'}`,
    `Формула: ${selectedFormula.value?.name ?? '—'}`,
    `Об'єм: ${volume.value} мл`,
    `Кількість: ${quantity.value} шт`,
    `Упаковка: ${selectedPackaging.value?.name ?? '—'}`,
    `Етикетка: ${selectedLabel.value?.name ?? '—'}`,
    `Коробка: ${selectedBox.value?.name ?? '—'}`,
  ]

  if (hasPricing.value) {
    lines.push(
      `Орієнтовна ціна за одиницю: ${unitPriceRange.value}`,
      `Орієнтовна сума партії: ${batchTotalRange.value}`,
    )
    if (discountPercent.value > 0) {
      lines.push(`Знижка за тираж: ${discountPercent.value}%`)
    }
    if (borderlineMinimum.value) {
      lines.push(`Увага: партія на межі мінімальної (${money(minBatchTotal.value)} ₴)`)
    } else if (belowMinimum.value) {
      lines.push(`Увага: партія менша за мінімальну (${money(minBatchTotal.value)} ₴)`)
    }
  }

  return lines.join('\n')
})

// Позиція «бульбашки» над повзунком: поправка на діаметр повзунка (28px),
// інакше на краях шкали підпис від'їжджає від самого повзунка.
function bubbleStyle(value: number, min: number, max: number) {
  const percent = (value - min) / (max - min)
  return { left: `calc(${percent * 100}% + ${(0.5 - percent) * 28}px)` }
}

// Конфігурація живе в URL: клієнт може повернутись до розрахунку або
// переслати його, а менеджер — надіслати готове посилання.
const route = useRoute()
const router = useRouter()

onMounted(() => {
  const q = route.query
  const num = (v: unknown) => (v === undefined ? null : Number(v))

  if (num(q.product)) productId.value = num(q.product)
  if (num(q.formula)) formulaId.value = num(q.formula)
  if (num(q.packaging)) packagingId.value = num(q.packaging)
  if (num(q.label)) labelId.value = num(q.label)
  if (num(q.box)) boxId.value = num(q.box)
  if (num(q.volume)) volume.value = Math.min(Math.max(num(q.volume)!, 10), 100)
  if (num(q.qty)) quantity.value = Math.min(Math.max(num(q.qty)!, QTY_MIN), QTY_MAX)

  // Слідкуємо за станом лише після відновлення з URL, інакше перший же
  // watch затер би параметри, з якими прийшов користувач.
  watch(
    [productId, formulaId, packagingId, labelId, boxId, volume, quantity],
    () => {
      router.replace({
        query: {
          ...route.query,
          product: productId.value ?? undefined,
          formula: formulaId.value ?? undefined,
          packaging: packagingId.value ?? undefined,
          label: labelId.value ?? undefined,
          box: boxId.value ?? undefined,
          volume: volume.value,
          qty: quantity.value,
        },
      })
    },
  )
})

const calculatorEl = ref<HTMLElement | null>(null)

// Кнопка «Розрахувати вартість» під товаром має підставляти цей товар
// у калькулятор, а не просто кидати користувача на початок секції.
function calculateFor(product: { name?: string }) {
  const matched = productTypes.value.find(
    type => product.name?.toLowerCase().includes(type.name.toLowerCase()),
  )
  if (matched) productId.value = matched.id
  calculatorEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// Опитування не живе окремо від калькулятора: обраний тип продукту і тираж
// одразу підставляються в розрахунок.
const quizSummary = ref('')

function onQuizFinish(payload: { summary: string, product: string | null, quantity: number | null }) {
  quizSummary.value = payload.summary

  const matched = productTypes.value.find(type => type.name === payload.product)
  if (matched) productId.value = matched.id
  if (payload.quantity) {
    quantity.value = Math.min(Math.max(payload.quantity, QTY_MIN), QTY_MAX)
  }
}

// --- Таймер акції на пробники ---
// Дедлайн — кінець поточного тижня. На сервері рендеримо прочерки:
// час до дедлайну на SSR і на клієнті різний, і це давало б розбіжність гідратації.
const countdown = ref({ days: '—', hours: '—', minutes: '—', seconds: '—' })

onMounted(() => {
  const deadline = new Date()
  deadline.setDate(deadline.getDate() + ((7 - deadline.getDay()) % 7))
  deadline.setHours(23, 59, 59, 999)

  function tick() {
    const left = Math.max(0, deadline.getTime() - Date.now())
    const seconds = Math.floor(left / 1000)
    countdown.value = {
      days: String(Math.floor(seconds / 86400)),
      hours: String(Math.floor((seconds % 86400) / 3600)),
      minutes: String(Math.floor((seconds % 3600) / 60)),
      seconds: String(seconds % 60),
    }
  }

  tick()
  const timer = setInterval(tick, 1000)
  onUnmounted(() => clearInterval(timer))
})

// --- Засоби для випуску під вашим брендом ---
// Вкладки будуються з реальних категорій каталогу, а не з хардкоду макета.
const activeCategory = ref<string>('')

const { data: catalog } = await useFetch<any>(`${config.public.apiBase}/catalog`)
const categories = computed<any[]>(() => catalog.value?.categories ?? [])

watchEffect(() => {
  if (!activeCategory.value && categories.value.length) {
    activeCategory.value = categories.value[0].slug
  }
})

const { data: brandProducts, pending: brandProductsPending } = await useAsyncData<any[]>(
  'contract-brand-products',
  async () => {
    if (!activeCategory.value) return catalog.value?.products?.data?.slice(0, 9) ?? []
    const res = await $fetch<any>(`${config.public.apiBase}/catalog`, {
      query: { category: activeCategory.value },
    })
    return res?.products?.data?.slice(0, 9) ?? []
  },
  { watch: [activeCategory], default: () => [] },
)
</script>

<template>
<div class="kontractne_vyrobnyctvo_page">
  <!-- Сторінка довга: на мобільному форма швидко зникає з поля зору. -->
  <div class="mobile-cta">
    <a href="#consultation" class="mobile-cta__btn">Отримати консультацію</a>
  </div>

  <section class="breadrembs-section">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><NuxtLink to="/">Головна</NuxtLink></li>
          <li class="breadcrumb-item active" aria-current="page">Контрактне виробництво</li>
        </ol>
      </nav>
    </div>
  </section>

  <section class="consultation-section">
    <div class="container">
      <div class="consultation-grid">
        <div class="left">
          <h1 class="title">Замовляйте власний бренд косметики «під ключ»</h1>
          <div class="content">Від розробки рецептури до дизайну етикетки</div>
          <div class="blue_tags_wrapper">
            <div class="blue_tag rounded-pill">від 7500 грн</div>
            <div class="blue_tag rounded-pill">за 15 днів</div>
            <div class="blue_tag rounded-pill">дизайн — безкоштовно</div>
          </div>
        </div>

        <div class="consultation-form">
          <h2>Запишіться на персональну консультацію</h2>
          <p>Наш фахівець допоможе підібрати ідеальні засоби Pelovit саме для ваших потреб та розповість, як отримати максимальний ефект.</p>
          <LeadForm source="contract" :contact-methods="contactMethods" />
          <ContactSocials />
        </div>
      </div>
    </div>
  </section>

  <section class="work_for own_brands">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-2">Створюємо власні бренди косметики «під ключ»</h2>
      </div>
      <div class="own_brands_title">Працюємо для:</div>

      <div class="row cards">
        <div v-for="item in audiences" :key="item.name" class="cat_card">
          <div class="category-card">
            <AppPicture :src="item.image" :alt="item.name" img-class="rounded-4 w-100" />
            <p class="mt-3 fw-medium cat_name">{{ item.name }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="in_posluga">
    <div class="container">
      <div class="title_posluga">У послугу входить:</div>
      <div class="posluga_wrapper">
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M63.3337 13.3334V66.6667H23.3337C21.5655 66.6667 19.8699 65.9643 18.6196 64.7141C17.3694 63.4638 16.667 61.7682 16.667 60V20C16.667 18.2319 17.3694 16.5362 18.6196 15.286C19.8699 14.0358 21.5655 13.3334 23.3337 13.3334H63.3337Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M63.3337 53.3334H23.3337C21.5655 53.3334 19.8699 54.0358 18.6196 55.286C17.3694 56.5362 16.667 58.2319 16.667 60" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M30 26.6666H50" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Рецептури</div>
        </div>
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M66.25 20.9001C67.3956 21.5516 68.3469 22.4966 69.006 23.6379C69.6652 24.7792 70.0082 26.0755 70 27.3934V51.6734C70 54.3701 68.5233 56.8567 66.14 58.1667L43.64 72.4001C42.5245 73.0125 41.2725 73.3336 40 73.3336C38.7275 73.3336 37.4755 73.0125 36.36 72.4001L13.86 58.1667C12.6939 57.5295 11.7204 56.5906 11.0414 55.4483C10.3623 54.306 10.0027 53.0023 10 51.6734V27.3901C10 24.6934 11.4767 22.2101 13.86 20.9001L36.36 7.63339C37.5085 7.00018 38.7986 6.66809 40.11 6.66809C41.4215 6.66809 42.7115 7.00018 43.86 7.63339L66.36 20.9001H66.25Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M51.667 31.4067C52.707 32.0067 53.3437 33.1234 53.3337 34.3267V45.25C53.3337 46.4634 52.677 47.5834 51.617 48.1734L41.617 54.58C41.1223 54.8544 40.566 54.9983 40.0003 54.9983C39.4347 54.9983 38.8783 54.8544 38.3837 54.58L28.3837 48.1734C27.8627 47.8845 27.4287 47.4612 27.1268 46.9477C26.8249 46.4341 26.6661 45.8491 26.667 45.2534V34.3267C26.667 33.1134 27.3237 31.9934 28.3803 31.4034L38.3803 25.4367C39.417 24.8567 40.6803 24.8567 41.7137 25.4367L51.7137 31.4034H51.667V31.4067Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Виробництво засобу</div>
        </div>
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M39.9997 10L66.6663 25V55L39.9997 70L13.333 55V25L39.9997 10Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 40L66.6667 25" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 40V70" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M39.9997 40L13.333 25" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M53.3337 17.5L26.667 32.5" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Підбір та замовлення упаковки</div>
        </div>
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M63.333 10H49.9997C48.2316 10 46.5359 10.7024 45.2856 11.9526C44.0354 13.2029 43.333 14.8986 43.333 16.6667V56.6667C43.333 60.2029 44.7378 63.5943 47.2383 66.0948C49.7387 68.5952 53.1301 70 56.6663 70C60.2026 70 63.5939 68.5952 66.0944 66.0948C68.5949 63.5943 69.9997 60.2029 69.9997 56.6667V16.6667C69.9997 14.8986 69.2973 13.2029 68.0471 11.9526C66.7968 10.7024 65.1011 10 63.333 10Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M43.3332 24.5L36.6666 17.8334C35.4164 16.5836 33.721 15.8815 31.9532 15.8815C30.1855 15.8815 28.4901 16.5836 27.2399 17.8334L17.8132 27.26C16.5634 28.5102 15.8613 30.2056 15.8613 31.9734C15.8613 33.7411 16.5634 35.4365 17.8132 36.6867L47.8132 66.6867" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M24.3333 43.3334H16.6667C14.8986 43.3334 13.2029 44.0358 11.9526 45.286C10.7024 46.5362 10 48.2319 10 50V63.3334C10 65.1015 10.7024 66.7972 11.9526 68.0474C13.2029 69.2977 14.8986 70 16.6667 70H56.6667" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M56.667 56.6666V56.7" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Дизайн та друк етикетки</div>
        </div>
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M23.3337 55L6.66699 45L23.3337 35L40.0003 45V63.3333L23.3337 73.3333V55Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6.66699 45V63.3333L23.3337 73.3333" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M23.333 55.15L39.9997 45.05" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M56.6667 55L40 45L56.6667 35L73.3333 45V63.3333L56.6667 73.3333V55Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 63.3334L56.6667 73.3334" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M56.667 55L73.3337 45" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M39.9997 45V26.6666L23.333 16.6666L39.9997 6.66663L56.6663 16.6666V35" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M23.333 16.7667V34.9501" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M40 26.6666L56.6667 16.6666" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Фасування, маркування та поклейка</div>
        </div>
        <div class="posluga_card">
          <i class="svg_posluga">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" aria-hidden="true">
              <path d="M30 50C30 52.6522 31.0536 55.1957 32.9289 57.0711C34.8043 58.9464 37.3478 60 40 60C42.6522 60 45.1957 58.9464 47.0711 57.0711C48.9464 55.1957 50 52.6522 50 50C50 47.3478 48.9464 44.8043 47.0711 42.9289C45.1957 41.0536 42.6522 40 40 40C37.3478 40 34.8043 41.0536 32.9289 42.9289C31.0536 44.8043 30 47.3478 30 50Z" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M33.333 23.3334H46.6663" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M33.333 60V73.3333L39.9997 70L46.6663 73.3333V60" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M33.3333 63.3333H26.6667C24.8986 63.3333 23.2029 62.631 21.9526 61.3807C20.7024 60.1305 20 58.4348 20 56.6667V16.6667C20 14.8986 20.7024 13.2029 21.9526 11.9526C23.2029 10.7024 24.8986 10 26.6667 10H53.3333C55.1014 10 56.7971 10.7024 58.0474 11.9526C59.2976 13.2029 60 14.8986 60 16.6667V56.6667C60 58.4348 59.2976 60.1305 58.0474 61.3807C56.7971 62.631 55.1014 63.3333 53.3333 63.3333H46.6667" stroke="#1A1A1A" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </i>
          <div class="card_content">Сертифікація</div>
        </div>
      </div>
    </div>
  </section>

  <section id="calculator" ref="calculatorEl" class="calculator_section py-5">
    <div class="calculator-container container">
      <div class="row g-5">

        <div class="col-lg-7">
          <h2 class="mb-5 fw-bold">Розрахувати вартість продукту</h2>

          <div class="mb-4">
            <label class="form-label" for="calc-product">Обрати продукт (вид продукту)</label>
            <select id="calc-product" v-model="productId" class="form-select form-select-lg bg-white">
              <option v-for="type in productTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="calc-formula">Обрати складність формули</label>
            <select id="calc-formula" v-model="formulaId" class="form-select form-select-lg bg-white">
              <option v-for="type in formulaTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="calc-volume">Обрати об'єм</label>
            <div class="range-wrap">
              <div class="range-bubble" :style="bubbleStyle(volume, 10, 100)">{{ volume }} ml</div>
              <input id="calc-volume" v-model.number="volume" type="range" class="form-range custom-range" min="10" max="100" step="10">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="calc-quantity">Обрати кількість</label>
            <div class="range-wrap">
              <div class="range-bubble" :style="bubbleStyle(quantity, QTY_MIN, QTY_MAX)">{{ quantity }} шт</div>
              <input
                id="calc-quantity"
                v-model.number="quantity"
                type="range"
                class="form-range custom-range"
                :min="QTY_MIN"
                :max="QTY_MAX"
                step="10"
              >
            </div>
          </div>

          <div class="mb-5">
            <label class="form-label mb-3" id="calc-packaging-label">Обрати упаковку</label>
            <div class="row g-3" role="radiogroup" aria-labelledby="calc-packaging-label">
              <div v-for="pack in packagings" :key="pack.id" class="col-4">
                <div
                  class="packaging-card text-center"
                  :class="{ active: packagingId === pack.id }"
                  role="radio"
                  :aria-checked="packagingId === pack.id"
                  tabindex="0"
                  @click="packagingId = pack.id"
                  @keydown.enter="packagingId = pack.id"
                  @keydown.space.prevent="packagingId = pack.id"
                >
                  <img :src="assetUrl(pack.image)" class="img-fluid" :alt="pack.name" loading="lazy" decoding="async">
                  <p class="small mt-2 mb-0">{{ pack.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label" for="calc-label">Обрати вид етикетки</label>
              <select id="calc-label" v-model="labelId" class="form-select">
                <option v-for="type in labelTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="calc-box">Потрібна коробка?</label>
              <select id="calc-box" v-model="boxId" class="form-select">
                <option v-for="type in boxTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="price-box mb-4">
            <div class="price_wrapper">
              <p class="mb-1">Ціна за одиницю:</p>
              <h2 class="fw-bold text-end">{{ hasPricing ? unitPriceRange : '—' }}</h2>
            </div>
            <div class="price_wrapper price_wrapper--total">
              <p class="mb-1">Партія {{ quantity }} шт:</p>
              <p class="price-total mb-1">{{ hasPricing ? batchTotalRange : '—' }}</p>
            </div>
            <p v-if="discountPercent > 0" class="price-discount mb-2">
              Враховано знижку за тираж —{{ discountPercent }}%
            </p>
            <p v-if="productionDays > 0" class="price-term mb-2">
              Термін виготовлення — {{ productionDays }} днів від затвердження етикетки
            </p>
            <small class="text-muted">
              *Розрахунок орієнтовний. Точна вартість фіксується після узгодження рецептури з технологом.
            </small>
          </div>

          <div v-if="belowMinimum" class="min-batch-warning mb-4">
            <p class="min-batch-warning__title">
              {{ borderlineMinimum ? 'Партія на межі мінімальної' : 'Партія менша за мінімальну' }}
            </p>
            <p v-if="borderlineMinimum" class="mb-0">
              Мінімальне замовлення — {{ money(minBatchTotal) }} ₴, а розрахунок дає вилку
              {{ batchTotalRange }}. Чи пройде партія, стане зрозуміло після узгодження рецептури —
              або збільште тираж до <b>{{ requiredQuantity }} шт</b>, щоб мати запас.
            </p>
            <p v-else-if="requiredExceedsMax" class="mb-0">
              Мінімальне замовлення — {{ money(minBatchTotal) }} ₴. За обраних параметрів такої суми
              не дає навіть максимальний тираж — збільште об'єм або оберіть іншу упаковку.
            </p>
            <p v-else class="mb-0">
              Мінімальне замовлення — {{ money(minBatchTotal) }} ₴.
              За обраних параметрів це приблизно <b>{{ requiredQuantity }} шт</b> (з урахуванням
              знижки за тираж) — збільште тираж або об'єм, і ми зможемо взяти замовлення в роботу.
            </p>
          </div>

          <div class="calc-summary mb-4">
            <p class="calc-summary__title">Ваша конфігурація</p>
            <ul class="calc-summary__list">
              <li><span>Продукт</span><b>{{ selectedProduct?.name ?? '—' }}</b></li>
              <li><span>Формула</span><b>{{ selectedFormula?.name ?? '—' }}</b></li>
              <li><span>Об'єм</span><b>{{ volume }} мл</b></li>
              <li><span>Кількість</span><b>{{ quantity }} шт</b></li>
              <li><span>Упаковка</span><b>{{ selectedPackaging?.name ?? '—' }}</b></li>
              <li><span>Етикетка</span><b>{{ selectedLabel?.name ?? '—' }}</b></li>
              <li><span>Коробка</span><b>{{ selectedBox?.name ?? '—' }}</b></li>
            </ul>
            <p class="calc-summary__note">Розрахунок і параметри підуть менеджеру разом із заявкою.</p>
          </div>

          <div class="consultation-form">
            <h2>Зв'язатись з нами</h2>
            <p>Наш фахівець допоможе підібрати ідеальні засоби Pelovit саме для ваших потреб та розповість, як отримати максимальний ефект.</p>
            <LeadForm source="contract-calculator" :contact-methods="contactMethods" :details="calculatorSummary" />
            <ContactSocials />
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="tabs_own_brand">
    <div class="container">
      <div class="container-fluid py-5">
        <div class="row">
          <h4 class="mb-4 px-2">Засоби для випуску під вашим брендом</h4>

          <div class="col-lg-3 col-xl-2 sidebar">
            <div class="nav flex-column px-2" role="tablist">
              <a
                v-for="category in categories"
                :key="category.id"
                href="#"
                class="nav-link"
                role="tab"
                :aria-selected="activeCategory === category.slug"
                :class="{ active: activeCategory === category.slug }"
                @click.prevent="activeCategory = category.slug"
              >{{ category.name }}</a>
            </div>
          </div>

          <div class="col-lg-9 col-xl-10">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" :class="{ 'is-loading': brandProductsPending }">
              <div v-for="product in brandProducts" :key="product.id" class="col">
                <div class="product-card h-100">
                  <NuxtLink :to="`/product/${product.slug}`" class="product-img">
                    <img :src="assetUrl(product.image)" :alt="product.name" loading="lazy" decoding="async">
                  </NuxtLink>
                  <div class="card_content py-2">
                    <NuxtLink :to="`/product/${product.slug}`" class="product-link">
                      <p class="mb-2 truncate">{{ product.name }}</p>
                    </NuxtLink>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                      <span class="tag_brand">Активи</span>
                      <span class="tag_brand">Ефект</span>
                      <span class="tag_brand">Склад</span>
                    </div>
                    <button type="button" class="btn calc-btn text-white w-100" @click="calculateFor(product)">
                      Розрахувати вартість
                    </button>
                  </div>
                </div>
              </div>
              <p v-if="!brandProductsPending && !brandProducts.length" class="text-muted">
                У цій категорії поки немає засобів.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="hero-section">
    <div class="container">
      <div class="row mb-3">
        <div class="col-lg-12">
          <h2 class="mb-4 title_contract">Аксимед пропонує найкращі умови для контрактного виробництва</h2>
        </div>
      </div>

      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="beige-box">
            Розробка бренда під ключ: ви робите<br>замовлення — Аксимед робить все інше
          </div>
        </div>

        <div class="col-lg-6">
          <div class="benefit-item">
            <span><strong>7500 грн</strong> — мінімальна ціна партії одного препарату</span>
          </div>
          <div class="benefit-item">
            <span>З кожним клієнтом працює персональний менеджер</span>
          </div>
          <div class="benefit-item">
            <span>Розробка дизайну етикетки — безкоштовно</span>
          </div>
          <div class="benefit-item">
            <span>Від затвердження етикетки до готового продукту — 15 днів</span>
          </div>
        </div>
      </div>

      <div class="mt-5 hero-image">
        <AppPicture src="images/skincare_routine.png" alt="Косметика Аксимед" img-class="img-fluid" />
      </div>
    </div>
  </section>

  <section class="process-section">
    <div class="container">
      <div class="title">Як відбувається співпраця</div>

      <div class="process-grid">
        <div v-for="(step, i) in steps" :key="step" class="step-card">
          <div class="step-number">{{ i + 1 }}</div>
          <div class="step-text">{{ step }}</div>
        </div>
      </div>

      <div class="bottom-text">
        За 15 днів ваша мрія про власний бренд косметики стане реальністю!
      </div>
    </div>
  </section>

  <section class="tester-section">
    <div class="container">
      <div class="row g-5 align-items-center test_container">

        <div class="col-lg-7 left_side">
          <h2 class="display-5 fw-bold mb-4">Бажаєте спробувати дієвість<br>засобу?</h2>
          <p class="lead mb-5">
            Пройдіть коротке опитування, щоб ми краще зрозуміли ваші потреби та запропонували найкращі рішення.
          </p>

          <div class="offer-card mb-5">
            <div class="row align-items-center">
              <div class="col-md-4 text-center">
                <AppPicture src="images/amber_test.png" alt="Пробники продукції" img-class="img-fluid offer-card__image" />
              </div>
              <div class="col-md-7">
                <h5 class="fw-bold">ОТРИМАЙТЕ 3 безкоштовні пробники нашої продукції</h5>
                <p class="mb-3">До кінця акції лишилось:</p>
                <div class="row g-2 text-center">
                  <div class="col-3"><div class="timer-box"><h5>{{ countdown.days }}</h5> <small>Дні</small></div></div>
                  <div class="col-3"><div class="timer-box"><h5>{{ countdown.hours }}</h5> <small>Годин</small></div></div>
                  <div class="col-3"><div class="timer-box"><h5>{{ countdown.minutes }}</h5> <small>Хвилин</small></div></div>
                  <div class="col-3"><div class="timer-box"><h5>{{ countdown.seconds }}</h5> <small>Секунд</small></div></div>
                </div>
              </div>
            </div>
          </div>

          <ContractQuiz @finish="onQuizFinish" />
        </div>

        <div class="col-lg-5 right_side">
          <div id="tester-form" class="consultation-form">
            <h4>Замовити тестер</h4>
            <LeadForm source="contract-tester" :contact-methods="contactMethods" :details="quizSummary" />
            <ContactSocials />
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="unique-section">
    <div class="container">
      <h2 class="display-5 fw-bold mb-4">
        Косметика Аксимед має унікальний<br>запатентований інгредієнт
      </h2>

      <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
          <div class="beige-box">
            40 років досвіду + цілющі грязі Куяльнику = біостимулюючий екстракт Пеловіт
          </div>
        </div>
        <div class="col-lg-6">
          <p class="lead">
            Аксимед 40 років займається розробкою косметики на основі Пеловіту — лікувального екстракту грязі Куяльнику
          </p>
        </div>
      </div>

      <div class="main-image mb-5">
        <AppPicture src="images/makeup_pencil.png" alt="Пеловіт" img-class="img-fluid" />
      </div>

      <div class="table-responsive mt-5">
        <table class="table align-middle custom-table">
          <thead>
            <tr>
              <th class="feature-title text-center">Біостимулятор</th>
              <th class="feature-title text-center">Натуральний антибіотик</th>
              <th class="feature-title text-center">Імуномодулятор</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>покращує кровообіг у шкірі</td>
              <td>надає антибактеріальну дію</td>
              <td>знімає запалення</td>
            </tr>
            <tr>
              <td>стимулює оновлення тканин</td>
              <td>має протигрибковий ефект</td>
              <td>підвищує імунітет покривів</td>
            </tr>
            <tr>
              <td>сприяє росту клітин</td>
              <td>знищує віруси</td>
              <td>покращує стійкість шкіри до зовнішніх подразників</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bottom-box">
        Пеловіт + європейські активи = унікальні засоби під ваш запит
      </div>
    </div>
  </section>

  <!-- Кейси наповнюються в адмінці; поки їх немає — секція не показується,
       щоб не було порожнього блока з обіцянками. -->
  <section v-if="brandCases.length" class="cases-section">
    <div class="container">
      <h2 class="section-title">Бренди, які ми вже зробили</h2>
      <p class="cases-lead">Реальні лінійки, випущені під брендом наших клієнтів.</p>

      <div class="row g-4">
        <div v-for="item in brandCases" :key="item.id" class="col-lg-4 col-md-6">
          <article class="case-card h-100">
            <div v-if="item.image" class="case-card__image">
              <img :src="assetUrl(item.image)" :alt="item.brand_name" loading="lazy" decoding="async">
            </div>
            <div class="case-card__body">
              <h3 class="case-card__brand">{{ item.brand_name }}</h3>
              <p v-if="item.client_name" class="case-card__client">
                {{ item.client_name }}<span v-if="item.client_role">, {{ item.client_role }}</span>
              </p>
              <p v-if="item.description" class="case-card__text">{{ item.description }}</p>
              <p v-if="item.result" class="case-card__result">{{ item.result }}</p>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section v-if="testimonials.length" class="testimonials-section">
    <div class="container">
      <h2 class="section-title">Відгуки про співпрацю</h2>

      <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="7000">
        <div class="carousel-inner">
          <div v-for="(slide, i) in testimonialSlides" :key="i" class="carousel-item" :class="{ active: i === 0 }">
            <div class="row g-4">
              <div v-for="item in slide" :key="item.id" class="col-md-6">
                <div class="testimonial-card">
                  <div class="wrapper_review">
                    <div class="quote-text">{{ item.quote }}</div>
                    <div class="testimonial-text">{{ item.text }}</div>
                  </div>
                  <div class="client-info">
                    <img :src="assetUrl(item.image)" :alt="item.author_name" class="client-avatar" loading="lazy" decoding="async">
                    <div>
                      <strong>{{ item.author_name }}</strong><br>
                      <small>{{ item.author_role }}</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Попередній</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Наступний</span>
        </button>

        <!-- Індикатори після слайдів: у статичному потоці порядок у DOM визначає позицію -->
        <div class="carousel-indicators">
          <button
            v-for="(_, i) in testimonialSlides"
            :key="i"
            type="button"
            data-bs-target="#testimonialsCarousel"
            :data-bs-slide-to="i"
            :class="{ active: i === 0 }"
            :aria-current="i === 0"
            :aria-label="`Відгуки, слайд ${i + 1}`"
          ></button>
        </div>
      </div>
    </div>
  </section>

  <section class="about-section">
    <div class="container">
      <div class="row align-items-center g-5">
        <h3 class="display-5 fw-bold">Хто ми?</h3>

        <div class="col-lg-5">
          <p class="lead">
            Аксимед — це інноваційне підприємство, підрозділ Одеської регіональної академії наук.
            Він знаходиться в Одесі і працює з 1985 року. На базі підприємства науковцями академії
            багато років проводяться наукові дослідження, клінічні спостереження та розробляються
            запатентовані рецептури.
          </p>
        </div>

        <div class="col-lg-7">
          <div class="row g-4">
            <div class="col-md-4">
              <div class="stats-card">
                <div class="stats-number">1000</div>
                <div class="stats-label">Одиниць<br>виробляємо в день</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stats-card">
                <div class="stats-number">88</div>
                <div class="stats-label">Сертифікованих<br>засобів</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stats-card">
                <div class="stats-number">50</div>
                <div class="stats-label">Працівників</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mt-4">
        <div class="col-lg-6">
          <div class="lab-image">
            <AppPicture src="images/pexel_arnempodrez.jpg" alt="Науковець з колбою" img-class="img-fluid" />
          </div>
        </div>
        <div class="col-lg-6">
          <div class="lab-image">
            <AppPicture src="images/science_in_laboratory.png" alt="Лабораторія Аксимед" img-class="img-fluid" />
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-nagoroda">
    <div class="container">
      <h3 class="title_nagoroda text-center mb-5">Робота Аксимед неодноразово відмічена нагородами</h3>
      <div class="nagoroda_img">
        <AppPicture src="images/nagorods.png" alt="Нагороди Аксимед" img-class="img-fluid" />
      </div>
    </div>
  </section>

  <section class="cert-section">
    <div class="container">
      <h2 class="section-title">Аксимед має європейську сертифікацію GMP ISO 22716</h2>
      <div class="row g-5 justify-content-center">
        <div class="col-lg-5 col-md-6">
          <div class="cert-card">
            <AppPicture src="images/sertificate1.png" alt="Сертифікат GMP ISO 22716 — англійська версія" img-class="cert-image" />
          </div>
        </div>
        <div class="col-lg-5 col-md-6">
          <div class="cert-card">
            <AppPicture src="images/sertificate2.png" alt="Сертифікат GMP ISO 22716 — українська версія" img-class="cert-image" />
          </div>
        </div>
      </div>
    </div>
  </section>

  <section v-if="faqs.length" class="faq-section">
    <div class="container">
      <h2 class="section-title">Часті питання</h2>

      <div class="accordion faq-accordion" id="contractFaq">
        <div v-for="(faq, i) in faqs" :key="faq.id" class="accordion-item">
          <h3 class="accordion-header">
            <button
              class="accordion-button"
              :class="{ collapsed: i !== 0 }"
              type="button"
              data-bs-toggle="collapse"
              :data-bs-target="`#faq-${faq.id}`"
              :aria-expanded="i === 0"
              :aria-controls="`faq-${faq.id}`"
            >{{ faq.question }}</button>
          </h3>
          <div
            :id="`faq-${faq.id}`"
            class="accordion-collapse collapse"
            :class="{ show: i === 0 }"
            data-bs-parent="#contractFaq"
          >
            <div class="accordion-body">{{ faq.answer }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="consultation" class="develop_cons_section">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6 left-content">
          <!-- h2, а не h1: заголовок першого екрана вже займає єдиний h1 сторінки -->
          <h2>Розробка власного бренду<br>косметики — новий рівень<br>доходу</h2>
          <p class="lead" style="font-size: 1.2rem; line-height: 1.5;">
            Аксимед робить цей крок доступним. Замовляйте
            сьогодні та представляйте клієнтам засоби вашого
            бренду вже за 2 тижні.
          </p>
        </div>

        <div class="col-lg-6">
          <div class="consultation-form">
            <h4>Зв'язатись з нами</h4>
            <LeadForm source="contract" :contact-methods="contactMethods" :show-company="true" />
            <ContactSocials />
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</template>
