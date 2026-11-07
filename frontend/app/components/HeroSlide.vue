<script setup lang="ts">
// Один слайд героя. Винесено в компонент, щоб активний слайд можна було задати
// статичним класом `active` (див. index.vue), не керуючи ним реактивно через Vue:
// реактивний :class конфліктував з Bootstrap-каруселлю й відкочував слайд на перший.
const props = withDefaults(defineProps<{
  /**
   * Чи це перший слайд. Карусель тримає всі слайди в DOM одночасно, тож
   * якщо кожен віддає <h1>, на сторінці їх стає стільки ж, скільки банерів.
   * Заголовок першого лишаємо h1, решта — h2; вигляд не змінюється,
   * бо розмір задає клас display-3.
   */
  primary?: boolean
  banner: {
    title: string
    subtitle?: string | null
    image?: string | null
    button_text?: string | null
    link?: string | null
  }
}>(), { primary: false })

const { assetUrl } = useAsset()

const HERO_GRADIENT =
  'linear-gradient(90deg, rgba(0,0,0,.72) 0%, rgba(0,0,0,.55) 38%, rgba(0,0,0,.18) 72%, rgba(0,0,0,.08) 100%)'

const bgStyle = computed(() => {
  const img = props.banner.image ? assetUrl(props.banner.image) : assetUrl('images/gl_face.png')
  return `background-image: ${HERO_GRADIENT}, url(${img})`
})
</script>

<template>
  <div class="hero text-white" :style="bgStyle">
    <div class="container">
      <div class="col-lg-6 mob">
        <component :is="primary ? 'h1' : 'h2'" class="display-3 fw-bold mb-4">
          {{ banner.title }}
        </component>
        <p v-if="banner.subtitle" class="lead mb-5">{{ banner.subtitle }}</p>
        <NuxtLink
          v-if="banner.button_text && banner.link"
          :to="banner.link"
          class="btn btn-light btn-lg px-5 py-3 fw-medium rad-16"
        >
          {{ banner.button_text }}
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
