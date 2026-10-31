<script setup lang="ts">
const props = withDefaults(defineProps<{
  source: string
  showCompany?: boolean
  contactMethods?: string[]
  // Довільний контекст заявки (напр. конфігурація з калькулятора) —
  // менеджер отримує його в листі разом з контактами.
  details?: string
}>(), {
  showCompany: false,
  contactMethods: () => ['call', 'telegram', 'viber'],
  details: '',
})

const methodLabels: Record<string, string> = {
  call: 'Дзвінок',
  telegram: 'Telegram',
  viber: 'Viber',
  whatsapp: 'WhatsApp',
}

const config = useRuntimeConfig()
const { add: addToast } = useToast()

const form = reactive({
  name: '',
  phone: '',
  contact_method: 'call',
  company: '',
})
const loading = ref(false)
const success = ref(false)
const error = ref('')

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await $fetch(`${config.public.apiBase}/leads`, {
      method: 'POST',
      body: {
        name: form.name,
        phone: form.phone,
        contact_method: form.contact_method,
        company: props.showCompany ? form.company || undefined : undefined,
        details: props.details || undefined,
        source: props.source,
      },
    })
    success.value = true
    addToast('Дякуємо! Ми зв\'яжемося з вами.', 'info')
  } catch {
    error.value = 'Сталася помилка. Спробуйте ще раз.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div v-if="success" class="lead-success">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" class="lead-success__icon">
      <circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="1.5"/>
      <path d="M7.5 12l3 3 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <p>Дякуємо! Ми зв'яжемося з вами найближчим часом.</p>
  </div>
  <form v-else @submit.prevent="submit">
    <input v-model="form.name" type="text" class="width_input" placeholder="Ваше ім'я" required>
    <input v-model="form.phone" type="tel" class="width_input" placeholder="+38 (0..) ... ...." required>
    <input v-if="showCompany" v-model="form.company" type="text" class="width_input" placeholder="Назва компанії / продукту">
    <div class="contact-method">
      <p>Спосіб зв'язку</p>
      <label v-for="method in contactMethods" :key="method">
        <input type="radio" v-model="form.contact_method" :value="method"> {{ methodLabels[method] }}
      </label>
    </div>
    <p v-if="error" class="lead-error">{{ error }}</p>
    <button type="submit" class="submit-btn" :disabled="loading">
      {{ loading ? 'Надсилання...' : 'Надіслати' }}
    </button>
  </form>
</template>

<style scoped>
.lead-success {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 32px 0;
  color: var(--brand-700);
  text-align: center;
  font-size: 1.05rem;
  font-weight: 500;
}

.lead-success__icon {
  color: var(--text-subtle);
}

.lead-error {
  color: var(--danger);
  font-size: .875rem;
  margin-bottom: 8px;
}
</style>
