<script setup lang="ts">
const open = defineModel<boolean>({ required: true })

const config = useRuntimeConfig()
const { add: addToast } = useToast()

const email = ref('')
const name = ref('')
const loading = ref(false)
const error = ref('')
const promoCode = ref('')
const discountLabel = ref('')
const expiresAt = ref('')
const alreadySubscribed = ref(false)
const copied = ref(false)

const emailField = ref<HTMLInputElement | null>(null)

interface SubscribeResponse {
  success: boolean
  already_subscribed: boolean
  promo_code: string | null
  discount_label?: string
  expires_at?: string | null
}

function reset() {
  email.value = ''
  name.value = ''
  error.value = ''
  promoCode.value = ''
  discountLabel.value = ''
  expiresAt.value = ''
  alreadySubscribed.value = false
  copied.value = false
}

async function submit() {
  if (loading.value) return

  loading.value = true
  error.value = ''

  try {
    const res = await $fetch<SubscribeResponse>(`${config.public.apiBase}/subscribe`, {
      method: 'POST',
      body: { email: email.value, name: name.value || undefined },
    })

    promoCode.value = res.promo_code ?? ''
    discountLabel.value = res.discount_label ?? ''
    expiresAt.value = res.expires_at ?? ''
    alreadySubscribed.value = res.already_subscribed

    // Код міг не зберегтися в давніх підписок — тоді хоча б дякуємо.
    if (!promoCode.value) {
      addToast('Ви вже підписані на новини PELOVIT.', 'info')
      close()
    }
  }
  catch (e: any) {
    error.value = e?.data?.errors?.email?.[0]
      ?? 'Не вдалося підписатися. Перевірте пошту і спробуйте ще раз.'
  }
  finally {
    loading.value = false
  }
}

async function copyCode() {
  try {
    await navigator.clipboard.writeText(promoCode.value)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  }
  catch {
    // Буфер може бути недоступний (немає дозволу, http) — код видно на екрані.
    copied.value = false
  }
}

function close() {
  open.value = false
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}

watch(open, async (isOpen) => {
  if (isOpen) {
    reset()
    await nextTick()
    emailField.value?.focus()
  }
})

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition name="subscribe-fade">
      <div v-if="open" class="subscribe-overlay" @click.self="close">
        <div
          class="subscribe-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="subscribe-title"
        >
          <button type="button" class="subscribe-modal__close" aria-label="Закрити" @click="close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
          </button>

          <!-- Крок 2: код видано -->
          <div v-if="promoCode" class="subscribe-modal__done">
            <h3 id="subscribe-title">
              {{ alreadySubscribed ? 'Ви вже з нами' : 'Готово!' }}
            </h3>
            <p>
              {{ alreadySubscribed
                ? 'Ось ваш персональний промокод — він усе ще діє:'
                : 'Ваш персональний промокод на першу покупку:' }}
            </p>

            <div class="subscribe-code">
              <code>{{ promoCode }}</code>
              <button type="button" class="subscribe-code__copy" @click="copyCode">
                {{ copied ? 'Скопійовано' : 'Копіювати' }}
              </button>
            </div>

            <p class="subscribe-modal__hint">
              <template v-if="discountLabel">Знижка {{ discountLabel }}. </template>
              Одноразовий<template v-if="expiresAt">, діє до {{ expiresAt.split('-').reverse().join('.') }}</template>.
              Введіть його в кошику. Копію надіслали на пошту.
            </p>

            <NuxtLink to="/catalog" class="btn-register rad-16" @click="close">
              До каталогу
            </NuxtLink>
          </div>

          <!-- Крок 1: форма -->
          <form v-else @submit.prevent="submit">
            <h3 id="subscribe-title">Отримати персональну знижку</h3>
            <p>
              Підпишіться на новини — надішлемо промокод на першу покупку
              та спеціальні пропозиції.
            </p>

            <label class="subscribe-field">
              <span>Email</span>
              <input
                ref="emailField"
                v-model.trim="email"
                type="email"
                required
                autocomplete="email"
                placeholder="you@example.com"
              >
            </label>

            <label class="subscribe-field">
              <span>Ім'я <small>(необов'язково)</small></span>
              <input v-model.trim="name" type="text" autocomplete="given-name" placeholder="Як до вас звертатися">
            </label>

            <p v-if="error" class="subscribe-error">{{ error }}</p>

            <button type="submit" class="btn-register rad-16" :disabled="loading">
              {{ loading ? 'Надсилаємо…' : 'Отримати промокод' }}
            </button>

            <p class="subscribe-modal__hint">
              Без спаму. Відписатися можна будь-коли.
            </p>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
