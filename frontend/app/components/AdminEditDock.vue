<script setup lang="ts">
const { links } = useAdminEditLinks()

const open = ref(false)
const root = ref<HTMLElement | null>(null)

/** Одна ціль — сам «пігулок» і є посиланням, список зайвий. */
const single = computed(() => (links.value.length === 1 ? links.value[0] : null))

watch(links, () => {
  open.value = false
})

// Тост живе в тому ж куті (bottom: 24px). Поки док видимий, піднімаємо тост
// над ним — через змінну, щоб для звичайного покупця нічого не змінилося.
watch(
  () => links.value.length > 0,
  (visible) => {
    if (import.meta.client) {
      document.documentElement.style.setProperty('--toast-bottom', visible ? '88px' : '')
    }
  },
)

onBeforeUnmount(() => {
  if (import.meta.client) {
    document.documentElement.style.removeProperty('--toast-bottom')
  }
})

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    open.value = false
  }
}

function onPointerDown(e: Event) {
  if (open.value && root.value && !root.value.contains(e.target as Node)) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('keydown', onKeydown)
  document.addEventListener('pointerdown', onPointerDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeydown)
  document.removeEventListener('pointerdown', onPointerDown)
})
</script>

<template>
  <Teleport to="body">
    <div v-if="links.length" ref="root" class="admin-dock">
      <Transition name="admin-dock-list">
        <ul v-if="open && !single" class="admin-dock__list">
          <li v-for="link in links" :key="link.url">
            <a :href="link.url" target="_blank" rel="noopener" class="admin-dock__item">
              {{ link.label }}
            </a>
          </li>
        </ul>
      </Transition>

      <a
        v-if="single"
        :href="single.url"
        target="_blank"
        rel="noopener"
        class="admin-dock__btn"
        :title="`Редагувати: ${single.label}`"
      >
        <span class="admin-dock__icon" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <span class="admin-dock__text">{{ single.label }}</span>
      </a>

      <button
        v-else
        type="button"
        class="admin-dock__btn"
        :aria-expanded="open"
        aria-label="Редагувати в адмінці"
        @click="open = !open"
      >
        <span class="admin-dock__icon" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <span class="admin-dock__text">Адмінка</span>
        <span class="admin-dock__count">{{ links.length }}</span>
      </button>
    </div>
  </Teleport>
</template>
