import api from './api'
import { slugify } from '@utils/slugify'

export const DEFAULT_NEWS_PAGE_CONFIG = Object.freeze({
  title: 'Tin tức & Sự kiện',
  description:
    'Cập nhật hoạt động doanh nghiệp, xu hướng thị trường và những bước tiến của IDI Seafood.',
  seo: {
    title: 'Tin tức & Sự kiện | IDI Seafood',
    description:
      'Cập nhật tin tức mới nhất về IDI Seafood, thị trường cá tra và phát triển bền vững.',
    keywords: '',
  },
  itemsPerPage: 12,
  categoryItemsPerPage: 10,
  featuredLimit: 3,
  relatedLimit: 6,
  showFeaturedSection: true,
  showCategoryNavigation: true,
  showRelatedArticles: true,
  showAuthor: true,
  showPublishedDate: true,
  showViewCount: true,
  showReadingTime: true,
  showTags: true,
  showArticleSource: true,
  showBreadcrumb: true,
  showSocialShare: true,
  showPreviousNext: true,
  showPlaceholderImage: true,
  allowPrint: true,
  lazyLoadImages: true,
})

const BLOCK_TAGS = new Set([
  'ADDRESS', 'ARTICLE', 'ASIDE', 'BLOCKQUOTE', 'DIV', 'DL', 'FIELDSET', 'FIGCAPTION',
  'FIGURE', 'FOOTER', 'FORM', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'HEADER', 'HR',
  'LI', 'MAIN', 'NAV', 'OL', 'P', 'PRE', 'SECTION', 'TABLE', 'UL', 'VIDEO',
])

const IGNORED_TAGS = new Set([
  'APPLET', 'BUTTON', 'CANVAS', 'FORM', 'INPUT', 'LINK', 'META', 'NOSCRIPT',
  'OBJECT', 'SCRIPT', 'SELECT', 'STYLE', 'SVG', 'TEMPLATE', 'TEXTAREA',
])

const compactText = value => String(value ?? '').replace(/\s+/g, ' ').trim()

function valueFrom(source, camelKey, snakeKey, fallback) {
  return source?.[camelKey] ?? source?.[snakeKey] ?? fallback
}

function numberFrom(value, fallback = 0) {
  const number = Number(value)
  return Number.isFinite(number) ? number : fallback
}

function booleanFrom(value, fallback) {
  if (value === undefined || value === null) return fallback
  if (typeof value === 'string') return !['0', 'false', 'off', 'no'].includes(value.toLowerCase())
  return Boolean(value)
}

function safeUrl(value, { image = false } = {}) {
  if (!value) return ''
  const url = String(value).trim()
  if (!url) return ''

  if (image && /^data:image\/(?:png|jpe?g|gif|webp);base64,[a-z0-9+/=\s]+$/i.test(url)) {
    return url
  }

  if (/^(?:\/|\.\/|\.\.\/|#|\?)/.test(url)) return url

  try {
    const parsed = new URL(url, typeof window === 'undefined' ? 'https://idiseafood.com' : window.location.origin)
    const allowedProtocols = image
      ? ['http:', 'https:', 'blob:']
      : ['http:', 'https:', 'mailto:', 'tel:']
    return allowedProtocols.includes(parsed.protocol) ? url : ''
  } catch {
    return ''
  }
}

function safeMediaUrl(value) {
  const url = safeUrl(value, { image: true })
  if (!url || !url.startsWith('/') || url.startsWith('//')) return url

  try {
    const frontendOrigin = typeof window === 'undefined' ? 'https://idiseafood.com' : window.location.origin
    const apiBase = new URL(api.defaults.baseURL || '/api', frontendOrigin)
    return new URL(url, apiBase.origin).href
  } catch {
    return url
  }
}

function normalizeInlineNodes(nodes) {
  const result = []

  Array.from(nodes ?? []).forEach((node) => {
    if (node.nodeType === 3) {
      const text = node.textContent?.replace(/\s+/g, ' ')
      if (text) result.push({ type: 'text', text })
      return
    }

    if (node.nodeType !== 1) return

    const tag = node.tagName
    if (IGNORED_TAGS.has(tag) || tag === 'IMG') return
    const children = normalizeInlineNodes(node.childNodes)

    if (tag === 'BR') {
      result.push({ type: 'break' })
      return
    }

    if (tag === 'A') {
      const href = safeUrl(node.getAttribute('href'))
      if (href) {
        result.push({
          type: 'link',
          href,
          external: /^https?:\/\//i.test(href),
          children,
        })
      } else {
        result.push(...children)
      }
      return
    }

    const inlineTypes = {
      B: 'strong',
      STRONG: 'strong',
      EM: 'emphasis',
      I: 'emphasis',
      U: 'underline',
      CODE: 'code',
      SUB: 'subscript',
      SUP: 'superscript',
    }

    if (inlineTypes[tag]) {
      result.push({ type: inlineTypes[tag], children })
      return
    }

    result.push(...children)
  })

  return result.reduce((items, item) => {
    const previous = items[items.length - 1]
    if (item.type === 'text' && previous?.type === 'text') {
      previous.text += item.text
    } else {
      items.push(item)
    }
    return items
  }, [])
}

function imageBlock(element, caption = '') {
  const url = safeMediaUrl(element?.getAttribute('src'))
  if (!url) return null

  return {
    type: 'image',
    url,
    alt: compactText(element.getAttribute('alt')),
    caption: compactText(caption || element.getAttribute('title')),
  }
}

function tableBlock(element) {
  const rows = Array.from(element.querySelectorAll('tr')).map(row => (
    Array.from(row.querySelectorAll(':scope > th, :scope > td')).map(cell => compactText(cell.textContent))
  )).filter(row => row.length)

  if (!rows.length) return null

  const firstRowHasHeaders = Boolean(element.querySelector('tr:first-child th'))
  return {
    type: 'table',
    headers: firstRowHasHeaders ? rows[0] : [],
    rows: firstRowHasHeaders ? rows.slice(1) : rows,
  }
}

function contentBlocksFromNode(node, context) {
  if (node.nodeType === 3) {
    const text = compactText(node.textContent)
    return text ? [{ type: 'paragraph', text }] : []
  }
  if (node.nodeType !== 1) return []

  const tag = node.tagName
  if (IGNORED_TAGS.has(tag)) return []

  if (/^H[1-6]$/.test(tag)) {
    const text = compactText(node.textContent)
    if (!text) return []
    const preferredId = slugify(node.getAttribute('id') || text) || `noi-dung-${context.headingIndex + 1}`
    const occurrence = context.headingIds.get(preferredId) ?? 0
    context.headingIds.set(preferredId, occurrence + 1)
    context.headingIndex += 1
    return [{
      type: 'heading',
      level: Math.min(4, Math.max(2, numberFrom(tag.slice(1), 2))),
      id: occurrence ? `${preferredId}-${occurrence + 1}` : preferredId,
      text,
      children: normalizeInlineNodes(node.childNodes),
    }]
  }

  if (tag === 'P') {
    const images = Array.from(node.querySelectorAll('img')).map(image => imageBlock(image)).filter(Boolean)
    const inlineNodes = normalizeInlineNodes(
      Array.from(node.childNodes).filter(child => child.nodeType !== 1 || child.tagName !== 'IMG'),
    )
    const text = compactText(node.textContent)
    const paragraph = text ? [{ type: 'paragraph', text, children: inlineNodes }] : []
    return [...paragraph, ...images]
  }

  if (tag === 'UL' || tag === 'OL') {
    const items = Array.from(node.children)
      .filter(child => child.tagName === 'LI')
      .map(item => ({ text: compactText(item.textContent), children: normalizeInlineNodes(item.childNodes) }))
      .filter(item => item.text)
    return items.length ? [{ type: 'list', ordered: tag === 'OL', items }] : []
  }

  if (tag === 'BLOCKQUOTE') {
    const cite = node.querySelector('cite')
    const clone = node.cloneNode(true)
    clone.querySelectorAll('cite').forEach(item => item.remove())
    const text = compactText(clone.textContent)
    return text ? [{
      type: 'quote',
      text,
      children: normalizeInlineNodes(clone.childNodes),
      attribution: compactText(cite?.textContent),
    }] : []
  }

  if (tag === 'FIGURE') {
    const image = node.querySelector('img')
    const caption = node.querySelector('figcaption')?.textContent
    const block = imageBlock(image, caption)
    return block ? [block] : []
  }

  if (tag === 'IMG') {
    const block = imageBlock(node)
    return block ? [block] : []
  }

  if (tag === 'TABLE') {
    const block = tableBlock(node)
    return block ? [block] : []
  }

  if (tag === 'PRE') {
    const text = String(node.textContent ?? '').trim()
    return text ? [{ type: 'code', text }] : []
  }

  if (tag === 'HR') return [{ type: 'separator' }]

  if (tag === 'VIDEO') {
    const url = safeMediaUrl(node.getAttribute('src') || node.querySelector('source')?.getAttribute('src'))
    return url ? [{
      type: 'video',
      url,
      poster: safeMediaUrl(node.getAttribute('poster')),
      caption: compactText(node.getAttribute('title')),
    }] : []
  }

  if (tag === 'IFRAME') {
    const url = safeUrl(node.getAttribute('src'))
    return url ? [{ type: 'embed', url, title: compactText(node.getAttribute('title')) }] : []
  }

  const childBlocks = Array.from(node.childNodes).flatMap(child => contentBlocksFromNode(child, context))
  if (childBlocks.length) return childBlocks

  if (!BLOCK_TAGS.has(tag)) {
    const text = compactText(node.textContent)
    if (text) return [{ type: 'paragraph', text, children: normalizeInlineNodes(node.childNodes) }]
  }

  return []
}

/**
 * Converts trusted CMS HTML into an inert data structure. DOMParser never mounts
 * the HTML; React renders only the explicitly supported nodes and safe URL schemes.
 */
export function htmlToContentBlocks(html) {
  if (typeof html !== 'string' || !html.trim()) return []

  if (typeof DOMParser === 'undefined') {
    const text = compactText(html.replace(/<[^>]*>/g, ' '))
    return text ? [{ type: 'paragraph', text }] : []
  }

  const document = new DOMParser().parseFromString(html, 'text/html')
  const context = { headingIndex: 0, headingIds: new Map() }
  return Array.from(document.body.childNodes).flatMap(node => contentBlocksFromNode(node, context))
}

export function normalizeNewsCategory(payload) {
  if (!payload) return null
  if (typeof payload === 'string') {
    return { id: payload, code: '', slug: slugify(payload), name: payload, description: '', count: 0 }
  }

  const name = payload.name ?? payload.title ?? ''
  return {
    ...payload,
    id: payload.id ?? payload.code ?? payload.slug ?? name,
    code: payload.code ?? '',
    slug: payload.slug ?? slugify(name),
    name,
    description: payload.description ?? '',
    count: numberFrom(payload.count ?? payload.postsCount ?? payload.posts_count, 0),
  }
}

export function normalizeNewsPageConfig(payload) {
  const source = payload ?? {}
  const rawSeo = source.seo ?? {}
  const presentation = { ...(source.presentation ?? {}), ...source }
  const defaults = DEFAULT_NEWS_PAGE_CONFIG

  return {
    ...source,
    title: source.title ?? source.pageTitle ?? source.page_title ?? defaults.title,
    description: source.description ?? defaults.description,
    seo: {
      ...rawSeo,
      title: rawSeo.title ?? source.seoTitle ?? source.seo_title ?? defaults.seo.title,
      description:
        rawSeo.description ?? source.metaDescription ?? source.meta_description ?? defaults.seo.description,
      keywords: rawSeo.keywords ?? source.metaKeywords ?? source.meta_keywords ?? defaults.seo.keywords,
    },
    itemsPerPage: numberFrom(valueFrom(source, 'itemsPerPage', 'items_per_page', defaults.itemsPerPage), defaults.itemsPerPage),
    categoryItemsPerPage: numberFrom(valueFrom(source, 'categoryItemsPerPage', 'category_items_per_page', defaults.categoryItemsPerPage), defaults.categoryItemsPerPage),
    featuredLimit: numberFrom(valueFrom(source, 'featuredLimit', 'featured_limit', defaults.featuredLimit), defaults.featuredLimit),
    relatedLimit: numberFrom(valueFrom(source, 'relatedLimit', 'related_limit', defaults.relatedLimit), defaults.relatedLimit),
    showFeaturedSection: booleanFrom(valueFrom(presentation, 'showFeaturedSection', 'show_featured_section'), defaults.showFeaturedSection),
    showCategoryNavigation: booleanFrom(valueFrom(presentation, 'showCategoryNavigation', 'show_category_navigation'), defaults.showCategoryNavigation),
    showRelatedArticles: booleanFrom(valueFrom(presentation, 'showRelatedArticles', 'show_related_articles'), defaults.showRelatedArticles),
    showAuthor: booleanFrom(valueFrom(presentation, 'showAuthor', 'show_author'), defaults.showAuthor),
    showPublishedDate: booleanFrom(valueFrom(presentation, 'showPublishedDate', 'show_published_date'), defaults.showPublishedDate),
    showViewCount: booleanFrom(valueFrom(presentation, 'showViewCount', 'show_view_count'), defaults.showViewCount),
    showReadingTime: booleanFrom(valueFrom(presentation, 'showReadingTime', 'show_reading_time'), defaults.showReadingTime),
    showTags: booleanFrom(valueFrom(presentation, 'showTags', 'show_tags'), defaults.showTags),
    showArticleSource: booleanFrom(valueFrom(presentation, 'showArticleSource', 'show_article_source'), defaults.showArticleSource),
    showBreadcrumb: booleanFrom(valueFrom(presentation, 'showBreadcrumb', 'show_breadcrumb'), defaults.showBreadcrumb),
    showSocialShare: booleanFrom(valueFrom(presentation, 'showSocialShare', 'show_social_share'), defaults.showSocialShare),
    showPreviousNext: booleanFrom(valueFrom(presentation, 'showPreviousNext', 'show_previous_next'), defaults.showPreviousNext),
    showPlaceholderImage: booleanFrom(valueFrom(presentation, 'showPlaceholderImage', 'show_placeholder_image'), defaults.showPlaceholderImage),
    allowPrint: booleanFrom(valueFrom(presentation, 'allowPrint', 'allow_print'), defaults.allowPrint),
    lazyLoadImages: booleanFrom(valueFrom(presentation, 'lazyLoadImages', 'lazy_load_images'), defaults.lazyLoadImages),
  }
}

export function normalizeNewsArticle(payload) {
  const candidate = payload?.slug || payload?.title
    ? payload
    : payload?.data?.slug || payload?.data?.title
      ? payload.data
      : payload
  if (!candidate || typeof candidate !== 'object') return null

  const category = normalizeNewsCategory(candidate.category)
  const rawImage = candidate.image ?? candidate.featuredImage ?? candidate.thumbnail
  const image = rawImage
    ? typeof rawImage === 'string'
      ? { url: safeMediaUrl(rawImage), alt: candidate.imageAlt ?? candidate.title ?? '' }
      : {
          ...rawImage,
          url: safeMediaUrl(rawImage.url ?? rawImage.src),
          alt: rawImage.alt ?? candidate.imageAlt ?? candidate.title ?? '',
        }
    : null
  const rawAuthor = candidate.author
  const author = rawAuthor
    ? typeof rawAuthor === 'string'
      ? { name: rawAuthor, role: candidate.authorRole ?? '' }
      : { ...rawAuthor, name: rawAuthor.name ?? '', role: rawAuthor.role ?? candidate.authorRole ?? '' }
    : null
  const contentHtml = candidate.contentHtml ?? candidate.content_html ?? candidate.content ?? ''
  const tags = Array.isArray(candidate.tags)
    ? candidate.tags.map(tag => (typeof tag === 'string' ? tag : tag?.name)).filter(Boolean)
    : []
  const publishedAt = candidate.publishedAt ?? candidate.published_at ?? candidate.date ?? candidate.createdAt

  return {
    ...candidate,
    id: candidate.id ?? candidate.code ?? candidate.slug,
    code: candidate.code ?? '',
    locale: candidate.locale ?? 'vi',
    slug: candidate.slug ?? '',
    title: candidate.title ?? '',
    excerpt: candidate.excerpt ?? candidate.summary ?? candidate.description ?? '',
    contentHtml,
    content: Array.isArray(candidate.contentBlocks)
      ? candidate.contentBlocks
      : htmlToContentBlocks(contentHtml),
    category,
    categoryName: category?.name ?? '',
    categoryColor: category?.color ?? candidate.categoryColor ?? 'blue',
    image: image?.url ? image : null,
    imageUrl: image?.url ?? '',
    imageAlt: image?.alt ?? candidate.title ?? '',
    author,
    authorName: author?.name ?? '',
    authorRole: author?.role ?? '',
    publishedAt,
    date: publishedAt,
    updatedAt: candidate.updatedAt ?? candidate.updated_at,
    isFeatured: booleanFrom(candidate.isFeatured ?? candidate.is_featured ?? candidate.featured, false),
    featured: booleanFrom(candidate.isFeatured ?? candidate.is_featured ?? candidate.featured, false),
    readTime: Math.max(1, numberFrom(candidate.readTime ?? candidate.readingTime ?? candidate.read_time, 1)),
    tags,
    sourceUrl: safeUrl(candidate.sourceUrl ?? candidate.source_url),
    viewCount: numberFrom(candidate.viewCount ?? candidate.view_count, 0),
    seo: {
      ...(candidate.seo ?? {}),
      title: candidate.seo?.title ?? candidate.seoTitle ?? candidate.seo_title ?? '',
      description:
        candidate.seo?.description ?? candidate.metaDescription ?? candidate.meta_description ?? '',
      keywords: candidate.seo?.keywords ?? candidate.metaKeywords ?? candidate.meta_keywords ?? '',
    },
  }
}

function cleanParams(params) {
  return Object.fromEntries(Object.entries(params).filter(([, value]) => (
    value !== undefined && value !== null && value !== ''
  )))
}

function normalizeIndexResponse(payload, requested = {}) {
  const source = payload?.data && !Array.isArray(payload?.data) ? payload.data : payload
  const rawItems = Array.isArray(source?.items)
    ? source.items
    : Array.isArray(source?.results)
      ? source.results
      : Array.isArray(source)
        ? source
        : []
  const items = rawItems.map(normalizeNewsArticle).filter(Boolean)
  const featured = (Array.isArray(source?.featured) ? source.featured : [])
    .map(normalizeNewsArticle)
    .filter(Boolean)
  const limit = Math.max(1, numberFrom(source?.limit ?? requested.limit, DEFAULT_NEWS_PAGE_CONFIG.itemsPerPage))
  const total = Math.max(0, numberFrom(source?.total, items.length))

  return {
    items,
    featured,
    categories: (Array.isArray(source?.categories) ? source.categories : [])
      .map(normalizeNewsCategory)
      .filter(Boolean),
    pageConfig: normalizeNewsPageConfig(source?.pageConfig ?? source?.page_config),
    total,
    page: Math.max(1, numberFrom(source?.page ?? requested.page, 1)),
    limit,
    lastPage: Math.max(1, numberFrom(source?.lastPage ?? source?.last_page, Math.ceil(total / limit) || 1)),
  }
}

export function getNewsErrorStatus(error) {
  return error?.response?.status ?? error?.status ?? null
}

export const newsService = {
  async getAll({
    locale = 'vi',
    page = 1,
    limit,
    category,
    search,
    sort,
    featured,
    exclude,
  } = {}, { signal } = {}) {
    const params = cleanParams({ locale, page, limit, category, search, sort, featured, exclude })
    const response = await api.get('/news', { params, signal })
    return normalizeIndexResponse(response.data, params)
  },

  async getBySlug(slug, { locale = 'vi', signal } = {}) {
    const response = await api.get(`/news/${encodeURIComponent(slug)}`, {
      params: { locale },
      signal,
    })
    const payload = response.data ?? {}
    const article = normalizeNewsArticle(payload.data ?? payload)
    if (!article) {
      const error = new Error('Không tìm thấy bài viết')
      error.status = 404
      throw error
    }
    return {
      article,
      pageConfig: normalizeNewsPageConfig(payload.pageConfig ?? payload.page_config),
    }
  },

  async getRelated(article, limit, { locale = article?.locale ?? 'vi', signal } = {}) {
    if (!article) return []
    const response = await this.getAll({
      locale,
      page: 1,
      limit: Math.max(1, limit ?? DEFAULT_NEWS_PAGE_CONFIG.relatedLimit),
      category: article.category?.slug ?? article.category?.id,
      exclude: article.id ?? article.slug,
      sort: 'newest',
    }, { signal })
    return response.items.filter(item => item.slug !== article.slug)
  },

  async getFeatured({ locale = 'vi', limit = DEFAULT_NEWS_PAGE_CONFIG.featuredLimit } = {}, options = {}) {
    const response = await this.getAll({ locale, page: 1, limit, featured: true }, options)
    const candidates = response.featured.length ? response.featured : response.items
    return {
      items: candidates.filter(article => article.isFeatured || !response.featured.length).slice(0, limit),
      pageConfig: response.pageConfig,
    }
  },
}
