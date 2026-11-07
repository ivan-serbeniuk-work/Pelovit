<script setup lang="ts">
import { Capacitor } from '@capacitor/core'
import { Browser } from '@capacitor/browser'
import { useCartStore } from '~/stores/cart'
import type { NovaPoshtaCity, NovaPoshtaWarehouse } from '~/composables/useNovaPoshta'

useHead({ title: 'Оформлення замовлення — PELOVIT' })

const config = useRuntimeConfig()
const cartStore = useCartStore()
const { assetUrl } = useAsset()
const router = useRouter()
const { data: settings } = useSettings()
const { searchCities, getWarehouses } = useNovaPoshta()

const COD_FEE = 20

// ТИМЧАСОВО: доки не заданий ключ Нової пошти, дозволяємо вводити місто/відділення
// вручну (без вибору зі списку), щоб можна було протестувати оплату LiqPay.
// Повернути на true, коли запрацює автозаповнення Нової пошти.
const REQUIRE_NP = false

const cartTotal = computed(() => cartStore.total)
const codFee = computed(() => (form.payment_method === 'cod' ? COD_FEE : 0))
const orderTotal = computed(() => Math.max(0, cartTotal.value - cartStore.discount) + codFee.value)
const submitting = ref(false)
const errors = ref<Record<string, string>>({})

// Промокод
const promoInput = ref('')
const promoApplying = ref(false)
const promoMessage = ref('')
const promoOk = ref(false)

async function applyPromo() {
  const code = promoInput.value.trim()
  if (!code || promoApplying.value) return

  promoApplying.value = true
  promoMessage.value = ''
  try {
    const res = await $fetch<any>(`${config.public.apiBase}/promo/validate`, {
      method: 'POST',
      body: { code, subtotal: cartTotal.value },
    })
    if (res.valid) {
      cartStore.applyPromo({
        code: res.code,
        type: res.type,
        value: res.value,
        min_order_total: res.min_order_total ?? null,
      })
      promoOk.value = true
      promoMessage.value = res.message
    } else {
      cartStore.clearPromo()
      promoOk.value = false
      promoMessage.value = res.message
    }
  } catch {
    promoOk.value = false
    promoMessage.value = 'Не вдалося перевірити промокод'
  } finally {
    promoApplying.value = false
  }
}

function incQty(id: number) {
  const item = cartStore.items.find(i => i.id === id)
  if (item) cartStore.update(id, item.qty + 1)
}

function decQty(id: number) {
  const item = cartStore.items.find(i => i.id === id)
  if (item && item.qty > 1) cartStore.update(id, item.qty - 1)
}

function removeItem(id: number) {
  cartStore.remove(id)
}

function removePromo() {
  cartStore.clearPromo()
  promoInput.value = ''
  promoMessage.value = ''
  promoOk.value = false
}

const form = reactive({
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
  city: '',
  branch: '',
  payment_method: 'card',
  comment: '',
})

// У формі одне поле, а бекенд, листи й адмінка оперують окремими
// first_name/last_name — ділимо введене по першому пробілу.
const fullName = ref('')

function splitFullName(value: string) {
  const parts = value.trim().replace(/\s+/g, ' ').split(' ')
  return { first_name: parts.shift() ?? '', last_name: parts.join(' ') }
}

const nameError = computed(() => errors.value.first_name || errors.value.last_name)

function fmt(n: number) { return Math.round(n) + '₴' }

// Нова Пошта: місто
const cityRef = ref('')
const citySuggestions = ref<NovaPoshtaCity[]>([])
const cityDropdownOpen = ref(false)
let cityDebounce: ReturnType<typeof setTimeout> | undefined

function onCityInput() {
  cityRef.value = ''
  form.branch = ''
  warehouses.value = []

  clearTimeout(cityDebounce)
  cityDebounce = setTimeout(async () => {
    citySuggestions.value = await searchCities(form.city)
    cityDropdownOpen.value = citySuggestions.value.length > 0
  }, 300)
}

function selectCity(city: NovaPoshtaCity) {
  form.city = city.area ? `${city.name}, ${city.area}` : city.name
  cityRef.value = city.ref
  citySuggestions.value = []
  cityDropdownOpen.value = false
  delete errors.value.city

  form.branch = ''
  warehouses.value = []
  loadWarehouses()
}

function onCityBlur() {
  setTimeout(() => { cityDropdownOpen.value = false }, 150)
}

// Нова Пошта: відділення
const warehouses = ref<NovaPoshtaWarehouse[]>([])
const warehouseDropdownOpen = ref(false)
const warehousesLoading = ref(false)
const warehouseSelected = ref(false)

async function loadWarehouses() {
  if (!cityRef.value) return
  warehousesLoading.value = true
  try {
    warehouses.value = await getWarehouses(cityRef.value)
  } finally {
    warehousesLoading.value = false
  }
}

const filteredWarehouses = computed(() => {
  if (!form.branch.trim()) return warehouses.value
  const q = form.branch.toLowerCase()
  return warehouses.value.filter((w) => w.description.toLowerCase().includes(q))
})

function onWarehouseInput() {
  warehouseSelected.value = false
  warehouseDropdownOpen.value = true
}

function onWarehouseFocus() {
  warehouseDropdownOpen.value = true
}

function selectWarehouse(w: NovaPoshtaWarehouse) {
  form.branch = w.description
  warehouseSelected.value = true
  warehouseDropdownOpen.value = false
  delete errors.value.branch
}

function onWarehouseBlur() {
  setTimeout(() => { warehouseDropdownOpen.value = false }, 150)
}

function redirectToLiqPay(payload: { action_url: string; data: string; signature: string }) {
  const f = document.createElement('form')
  f.method = 'POST'
  f.action = payload.action_url
  f.acceptCharset = 'utf-8'
  for (const [name, value] of Object.entries({ data: payload.data, signature: payload.signature })) {
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = name
    input.value = value
    f.appendChild(input)
  }
  document.body.appendChild(f)
  f.submit()
}

async function submitOrder() {
  if (cartStore.items.length === 0) return

  errors.value = {}
  const localErrors: Record<string, string> = {}

  // Прізвище на бекенді обов'язкове, тож самого лише імені замало.
  const { first_name, last_name } = splitFullName(fullName.value)
  if (!first_name) localErrors.first_name = 'Вкажіть ім\'я та прізвище'
  else if (!last_name) localErrors.last_name = 'Вкажіть також прізвище'
  form.first_name = first_name
  form.last_name = last_name

  if (!form.phone.trim()) localErrors.phone = 'Вкажіть мобільний телефон'

  // Лист із підтвердженням і посиланням на статус — єдиний спосіб для покупця
  // стежити за замовленням, тож пошта обовʼязкова.
  if (!form.email.trim()) localErrors.email = 'Вкажіть електронну пошту'
  else if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) localErrors.email = 'Перевірте адресу пошти'

  if (REQUIRE_NP && !cityRef.value) localErrors.city = 'Оберіть місто зі списку Нової пошти'
  else if (!form.city.trim()) localErrors.city = 'Вкажіть місто'
  if (!form.branch.trim()) localErrors.branch = REQUIRE_NP ? 'Оберіть відділення або поштомат' : 'Вкажіть відділення'
  if (Object.keys(localErrors).length > 0) {
    errors.value = localErrors
    return
  }

  submitting.value = true

  try {
    const isNative = Capacitor.isNativePlatform()

    const resp = await $fetch<{
      order_id: number
      track_token?: string
      liqpay?: { action_url: string; data: string; signature: string }
      payment_url?: string
    }>(`${config.public.apiBase}/orders`, {
      method: 'POST',
      // За цим заголовком бекенд віддає посилання на оплату замість форми.
      headers: isNative ? { 'X-App-Platform': Capacitor.getPlatform() } : {},
      body: {
        ...form,
        items: cartStore.items,
        total: orderTotal.value,
        promo_code: cartStore.promo?.code ?? null,
      },
    })
    cartStore.clear()

    // У застосунку оплата відкривається в системному браузері: у WebView
    // ламається 3-D Secure. Повернення — по deep link, який ловить плагін.
    if (isNative && resp.payment_url) {
      await Browser.open({ url: resp.payment_url })
      return
    }

    // LiqPay: hand off to the payment gateway via an auto-submitted form.
    if (resp.liqpay) {
      redirectToLiqPay(resp.liqpay)
      return
    }

    router.push({
      path: '/order/success',
      query: { payment_method: form.payment_method, token: resp.track_token },
    })
  } catch (err: any) {
    if (err?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(err.data.errors).map(([k, v]: any) => [k, v[0]])
      )
      // Промокод міг стати недійсним між застосуванням і відправкою
      if (errors.value.promo_code) {
        promoOk.value = false
        promoMessage.value = errors.value.promo_code
        cartStore.clearPromo()
      }
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
<section class="container order_section">
  <h1 class="fw-bold mt-4 mb-3">Оформлення замовлення</h1>

  <form @submit.prevent="submitOrder">
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card checkout-card mb-4">
          <div class="card-header-custom">Контактні дані</div>
          <div class="card-body p-4 pt-0">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-medium">Ім'я та прізвище <span class="text-danger">*</span></label>
                <input v-model="fullName" type="text" class="form-control" :class="{'is-invalid': nameError}" placeholder="Введіть ім'я та прізвище" required>
                <div v-if="nameError" class="invalid-feedback">{{ nameError }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Мобільний телефон <span class="text-danger">*</span></label>
                <input v-model="form.phone" type="tel" class="form-control" :class="{'is-invalid': errors.phone}" placeholder="Введіть телефон" required>
                <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Електронна пошта <span class="text-danger">*</span></label>
                <input v-model="form.email" type="email" class="form-control" :class="{'is-invalid': errors.email}" placeholder="Введіть електронну пошту" required>
                <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                <div class="form-text">Надішлемо підтвердження та посилання на статус замовлення.</div>
              </div>
            </div>
          </div>
        </div>

        <div class="card checkout-card mb-4">
          <div class="card-header-custom">Спосіб доставки</div>
          <div class="card-body p-4 pt-0">
            <div class="d-flex align-items-center gap-2 mb-3"><span>Нова пошта</span></div>
            <div class="row g-3">
              <div class="col-md-6 position-relative">
                <label class="form-label">Місто / Населений пункт <span class="text-danger">*</span></label>
                <input
                  v-model="form.city"
                  type="text"
                  class="form-control"
                  :class="{'is-invalid': errors.city}"
                  placeholder="Почніть вводити назву міста"
                  autocomplete="off"
                  @input="onCityInput"
                  @focus="cityDropdownOpen = citySuggestions.length > 0"
                  @blur="onCityBlur"
                >
                <ul v-if="cityDropdownOpen" class="np-dropdown">
                  <li v-for="c in citySuggestions" :key="c.ref" @mousedown.prevent="selectCity(c)">
                    {{ c.name }} <span v-if="c.area" class="text-muted small">— {{ c.area }}</span>
                  </li>
                </ul>
                <div v-if="errors.city" class="invalid-feedback d-block">{{ errors.city }}</div>
                <div v-else-if="cityRef" class="text-success small mt-1">
                  <AppIcon name="check" /> Місто підтверджено Новою поштою
                </div>
              </div>
              <div class="col-md-6 position-relative">
                <label class="form-label">Номер відділення / поштомату <span class="text-danger">*</span></label>
                <input
                  v-model="form.branch"
                  type="text"
                  class="form-control"
                  :class="{'is-invalid': errors.branch}"
                  :placeholder="(!REQUIRE_NP || cityRef) ? 'Почніть вводити номер або адресу' : 'Спочатку оберіть місто'"
                  autocomplete="off"
                  :disabled="REQUIRE_NP && !cityRef"
                  @input="onWarehouseInput"
                  @focus="onWarehouseFocus"
                  @blur="onWarehouseBlur"
                >
                <ul v-if="warehouseDropdownOpen" class="np-dropdown">
                  <li v-if="warehousesLoading" class="text-muted">Завантаження…</li>
                  <template v-else>
                    <li v-for="w in filteredWarehouses" :key="w.ref" @mousedown.prevent="selectWarehouse(w)">
                      {{ w.description }}
                    </li>
                    <li v-if="filteredWarehouses.length === 0" class="text-muted">Нічого не знайдено</li>
                  </template>
                </ul>
                <div v-if="errors.branch" class="invalid-feedback d-block">{{ errors.branch }}</div>
                <div v-else-if="warehouseSelected" class="text-success small mt-1">
                  <AppIcon name="check" /> Відділення підтверджено Новою поштою
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card checkout-card mb-4">
          <div class="card-header-custom">Спосіб оплати</div>
          <div class="card-body p-4 pt-0">
            <div class="form-check mb-3 p-3">
              <input v-model="form.payment_method" class="form-check-input" type="radio" value="liqpay" id="liqpayPayment">
              <label class="form-check-label" for="liqpayPayment">Оплатити карткою онлайн</label>
              <div class="text-muted small mt-1">Безпечна оплата карткою через LiqPay. Після підтвердження ви перейдете на сторінку оплати.</div>
              <PaymentLogos />
            </div>
            <div class="form-check mb-3 p-3">
              <input v-model="form.payment_method" class="form-check-input" type="radio" value="card" id="cardPayment">
              <label class="form-check-label" for="cardPayment">Оплата на карту</label>
              <div class="text-muted small mt-1">Проведіть платіж безпосередньо на наш банківський рахунок.</div>
              <div v-if="form.payment_method === 'card' && settings?.payment_card_number" class="alert alert-light border mt-2 mb-0 py-2 px-3 small">
                Номер картки: <strong>{{ settings.payment_card_number }}</strong>
                <span v-if="settings.payment_card_holder"><br>Одержувач: <strong>{{ settings.payment_card_holder }}</strong></span>
              </div>
            </div>
            <div class="form-check p-3 rounded-3">
              <input v-model="form.payment_method" class="form-check-input" type="radio" value="cod" id="cashOnDelivery">
              <label class="form-check-label" for="cashOnDelivery">Оплата при отриманні</label>
              <div class="text-muted small mt-1">Накладений платіж + 20₴</div>
            </div>
            <div v-if="errors.payment_method" class="invalid-feedback d-block px-3">{{ errors.payment_method }}</div>
          </div>
        </div>

        <div class="card checkout-card mb-4">
          <div class="card-header-custom">Коментар до замовлення</div>
          <div class="card-body p-4 pt-0">
            <textarea v-model="form.comment" class="form-control" rows="3" placeholder="Залишити коментар"></textarea>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card checkout-card mb-4 sticky-lg-top" style="top: 20px;">
          <div class="card-header-custom pb-0">Деталі замовлення</div>
          <div class="card-body p-4">
            <div class="cartItemsList">
              <p v-if="cartStore.items.length === 0" class="text-muted">Кошик порожній</p>
              <div v-for="item in cartStore.items" :key="item.id"
                   class="cart-item d-flex align-items-center gap-3 bg-white p-2-5 rad-16 mb-2">
                <img :src="assetUrl(item.image)" :alt="item.name" class="product-img" loading="lazy" decoding="async">
                <div class="flex-grow-1">
                  <h6>{{ item.name }}</h6>
                  <!-- Передумав на останньому кроці — не треба вертатися
                       в кошик і проходити оформлення наново. -->
                  <div class="checkout-qty mt-1">
                    <button type="button" :disabled="item.qty <= 1" :aria-label="`Зменшити кількість: ${item.name}`" @click="decQty(item.id)">−</button>
                    <span>{{ item.qty }}</span>
                    <button type="button" :aria-label="`Збільшити кількість: ${item.name}`" @click="incQty(item.id)">+</button>
                    <button type="button" class="checkout-qty__remove" :aria-label="`Прибрати ${item.name}`" @click="removeItem(item.id)">Прибрати</button>
                  </div>
                </div>
                <div class="fw-medium">{{ fmt(parseFloat(String(item.price)) * item.qty) }}</div>
              </div>
            </div>

            <div class="promocode_block pt-3">
              <label class="form-label mb-1">Промокод</label>
              <div class="input-group">
                <input
                  v-model="promoInput"
                  type="text"
                  class="form-control"
                  placeholder="Введіть промокод"
                  :disabled="!!cartStore.promo"
                  @keyup.enter.prevent="applyPromo"
                >
                <button
                  v-if="!cartStore.promo"
                  type="button"
                  class="btn actualize px-3"
                  :disabled="promoApplying || !promoInput.trim()"
                  @click="applyPromo"
                >
                  {{ promoApplying ? '...' : 'Застосувати' }}
                </button>
                <button
                  v-else
                  type="button"
                  class="btn actualize px-3"
                  @click="removePromo"
                >
                  Прибрати
                </button>
              </div>
              <div v-if="promoMessage" class="small mt-1" :class="promoOk ? 'text-success' : 'text-danger'">
                {{ promoMessage }}
              </div>
              <!-- Кошик міг подешевшати після застосування коду: пояснюємо,
                   чому знижки зараз немає, замість того щоб мовчки її прибрати. -->
              <div v-if="cartStore.promoShortfall > 0" class="small mt-1 text-warning-emphasis">
                Додайте товарів ще на {{ fmt(cartStore.promoShortfall) }}, щоб знижка застосувалась.
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3">
              <span class="text-muted">Сума товарів</span>
              <span>{{ fmt(cartTotal) }}</span>
            </div>
            <div v-if="cartStore.discount > 0" class="d-flex justify-content-between align-items-center mt-2">
              <span class="text-muted">Знижка <span v-if="cartStore.promo">({{ cartStore.promo.code }})</span></span>
              <span class="text-success">−{{ fmt(cartStore.discount) }}</span>
            </div>
            <div v-if="codFee > 0" class="d-flex justify-content-between align-items-center mt-2">
              <span class="text-muted">Накладений платіж</span>
              <span>+{{ fmt(codFee) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center bottom_sum pt-3 mt-2">
              <span class="fw-semibold">Сума замовлення</span>
              <span class="fw-semibold">{{ fmt(orderTotal) }}</span>
            </div>

            <button type="submit" class="btn btn-checkout-primary w-100 text-white mt-4" :disabled="submitting || cartStore.items.length === 0">
              {{ submitting ? 'Оформлення...' : 'Підтвердити замовлення' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
</section>
</template>
