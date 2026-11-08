import { isSafeVideoSrc, videoMime, youtubeIdFromUrl } from './media'

const BLOCKED_TAGS = /<\/?(script|style|iframe|object|embed|form|input|link|meta)\b[^>]*>/gi
const BLOCKED_BLOCKS = /<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi
const EVENT_ATTRS = /\s+on[a-z]+\s*=\s*("[^"]*"|'[^']*'|[^\s>]+)/gi
const JS_URLS = /\s+(href|src)\s*=\s*("\s*javascript:[^"]*"|'\s*javascript:[^']*'|javascript:[^\s>]+)/gi

// Відео в тілі статті: редактор вставляє або <iframe> з YouTube, або <video>
// із залитим файлом. Решта регекспів нижче ріже обидва теги, тому їх треба
// дістати ДО чистки й повернути ПІСЛЯ неї.
const IFRAME = /<iframe\b[^>]*>(?:[\s\S]*?<\/iframe>)?/gi
const VIDEO = /<video\b[^>]*>[\s\S]*?<\/video>/gi
const SRC_ATTR = /\bsrc\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+))/i
const SOURCE_TAG = /<source\b[^>]*>/gi
// Залишки після повернення заглушок: битий <video> без закривашки під VIDEO
// не підпадає, а BLOCKED_TAGS його не ріже — дорізаємо тут.
const MEDIA_LEFTOVERS = /<\/?(video|source|track)\b[^>]*>/gi
// Обірваний тег у кінці рядка ('<video src="x"' без '>') під попередній
// регексп не підпадає: парсер браузера зробив би з нього порожній елемент.
const UNCLOSED_MEDIA = /<\/?(video|source|track)\b[^>]*$/i

function srcOf(tag: string): string | null {
  const m = SRC_ATTR.exec(tag)

  return m?.[1] ?? m?.[2] ?? m?.[3] ?? null
}

/**
 * Вставлене відео ми не «дозволяємо», а збираємо заново з нуля: з усього
 * тега беремо лише src, перевіряємо його окремим allowlist і друкуємо свій
 * розмітковий шаблон. Так жоден атрибут з бази (onerror, srcdoc, style…)
 * не доїжджає до DOM, і ручний регексп-санітайзер не доводиться робити
 * розумнішим, ніж він є.
 */
function rebuildIframe(tag: string): string {
  const id = youtubeIdFromUrl(srcOf(tag))
  if (!id) return ''

  return '<div class="ratio ratio-16x9 article-video">'
    + `<iframe src="https://www.youtube-nocookie.com/embed/${id}?rel=0" title="Відео" loading="lazy"`
    + ' referrerpolicy="strict-origin-when-cross-origin"'
    + ' allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"'
    + ' allowfullscreen class="rounded"></iframe></div>'
}

function rebuildVideo(tag: string): string {
  const candidates = [srcOf(tag), ...(tag.match(SOURCE_TAG) ?? []).map(srcOf)]
  const src = candidates.find(isSafeVideoSrc)
  if (!src) return ''

  return '<video controls preload="metadata" playsinline class="article-video rounded">'
    + `<source src="${src}" type="${videoMime(src)}"></video>`
}

/**
 * Прибирає з HTML небезпечні теги, інлайн-обробники та javascript:-посилання.
 * Описи товарів і тексти статей зберігаються як HTML, тому виводимо їх через
 * v-html — але тільки після цієї чистки.
 */
export function sanitizeHtml(html?: string | null): string {
  if (!html) return ''

  const kept: string[] = []
  // \u0000 не трапляється в контенті з редактора, і жоден регексп нижче
  // його не чіпає: усі вони прив'язані до '<', '>' або 'on…='.
  const stash = (markup: string) => {
    if (!markup) return ''
    kept.push(markup)

    return `\u0000M${kept.length - 1}\u0000`
  }

  let out = String(html)
    .replace(BLOCKED_BLOCKS, '')
    .replace(IFRAME, tag => stash(rebuildIframe(tag)))
    .replace(VIDEO, tag => stash(rebuildVideo(tag)))
    .replace(BLOCKED_TAGS, '')
    .replace(EVENT_ATTRS, '')
    .replace(JS_URLS, '')
    .replace(MEDIA_LEFTOVERS, '')
    .replace(UNCLOSED_MEDIA, '')

  out = out.replace(/\u0000M(\d+)\u0000/g, (_, i) => kept[Number(i)] ?? '')

  return out
}

/** Перетворює HTML на звичайний текст — для meta-описів і schema.org. */
export function htmlToText(html?: string | null, limit = 300): string {
  if (!html) return ''
  const text = String(html)
    .replace(BLOCKED_BLOCKS, '')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&quot;/gi, '"')
    .replace(/&#0?39;|&apos;/gi, "'")
    .replace(/&lt;/gi, '<')
    .replace(/&gt;/gi, '>')
    .replace(/\s+/g, ' ')
    .trim()
  return text.length > limit ? text.slice(0, limit - 1).trimEnd() + '…' : text
}
