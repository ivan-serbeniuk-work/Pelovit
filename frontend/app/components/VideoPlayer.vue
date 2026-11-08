<script setup lang="ts">
import { videoMime, youtubeIdFromUrl } from '~/utils/media'

/**
 * Один плеєр на весь сайт: залитий файл грає нативним <video>, посилання —
 * через youtube-nocookie. Раніше iframe був написаний руками на сторінці
 * товару, і додати відео в статтю означало скопіювати його ще раз.
 */
const props = withDefaults(defineProps<{
  src?: string | null
  videoUrl?: string | null
  poster?: string | null
  title?: string
  ratio?: boolean
}>(), { ratio: true })

const { assetUrl } = useAsset()

// Порожній fallback обов'язковий: за замовчуванням assetUrl підставляє
// картинку-заглушку, і <source> поїхав би на images/image.png.
const fileUrl = computed(() => props.src ? assetUrl(props.src, '') : '')
const posterUrl = computed(() => props.poster ? assetUrl(props.poster, '') : undefined)
const youtubeId = computed(() => youtubeIdFromUrl(props.videoUrl))
const mime = computed(() => videoMime(props.src))

// Свій файл має перевагу над YouTube: він швидший і без чужих лічильників.
const kind = computed(() => fileUrl.value ? 'file' : (youtubeId.value ? 'youtube' : 'none'))

const videoEl = ref<HTMLVideoElement | null>(null)

defineExpose({ pause: () => videoEl.value?.pause() })
</script>

<template>
<div v-if="kind !== 'none'" :class="ratio ? 'ratio ratio-16x9' : ''">
  <video
    v-if="kind === 'file'"
    ref="videoEl"
    controls
    preload="metadata"
    playsinline
    :poster="posterUrl"
    class="video-player rounded"
  >
    <source :src="fileUrl" :type="mime">
  </video>

  <iframe
    v-else
    :src="`https://www.youtube-nocookie.com/embed/${youtubeId}?rel=0`"
    :title="title ? `Відео: ${title}` : 'Відео'"
    loading="lazy"
    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    referrerpolicy="strict-origin-when-cross-origin"
    allowfullscreen
    class="rounded"
  />
</div>
</template>

<style scoped>
.video-player {
  width: 100%;
  height: 100%;
  /* contain, а не cover: знімають переважно вертикально, і cover різав
     такі ролики по боках. Темна підкладка робить поля навмисними. */
  object-fit: contain;
  background: var(--brand-700);
}
</style>
