/** ID ролика з будь-якого формату посилання — порт регекспа з App\Models\Concerns\HasYoutubeId. */
const YOUTUBE_ID = /(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|live\/)|youtu\.be\/)([\w-]{11})/

export function youtubeIdFromUrl(url?: string | null): string | null {
  if (!url) return null

  return YOUTUBE_ID.exec(String(url))?.[1] ?? null
}

/**
 * Чи можна віддати цей src у <video>. Свідомо вузький allowlist: тільки
 * http(s) або шлях від кореня, тільки .mp4/.webm, і жодного символу, яким
 * можна вийти з атрибута. data: і javascript: не проходять за визначенням —
 * вони не підпадають під шаблон.
 */
const SAFE_VIDEO_SRC = /^(?:https?:\/\/[\w.-]+(?::\d+)?)?\/[\w\-./%]+\.(mp4|webm)(?:\?[\w=&%.\-]*)?$/i
const UNSAFE_CHARS = /["'<>\\\s]/

export function isSafeVideoSrc(src?: string | null): boolean {
  if (!src) return false
  const value = String(src)

  return !UNSAFE_CHARS.test(value) && SAFE_VIDEO_SRC.test(value)
}

/** MIME за розширенням — потрібен атрибуту type у <source>. */
export function videoMime(src?: string | null): string {
  return String(src ?? '').toLowerCase().endsWith('.webm') ? 'video/webm' : 'video/mp4'
}
