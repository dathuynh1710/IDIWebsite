import api from './api'

export const DEFAULT_INVESTOR_PAGE_CONFIG = Object.freeze({
  title: 'Quan hệ cổ đông',
  description: 'Thông tin và tài liệu công bố dành cho cổ đông IDI.',
  seo: { title: 'Quan hệ cổ đông | IDI Seafood', description: '' },
  updatedAt: null,
})

const numberFrom = (value, fallback = 0) => {
  const number = Number(value)
  return Number.isFinite(number) ? number : fallback
}

const cleanParams = params => Object.fromEntries(
  Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== ''),
)

function downloadUrl(value) {
  if (!value) return ''
  const url = String(value).trim()
  if (!url) return ''

  try {
    const frontendOrigin = typeof window === 'undefined' ? 'http://localhost' : window.location.origin
    const apiBase = new URL(api.defaults.baseURL || '/api', frontendOrigin)
    return new URL(url, apiBase.origin).href
  } catch {
    return ''
  }
}

function normalizeDocument(document) {
  if (!document || typeof document !== 'object') return null

  return {
    ...document,
    id: document.id,
    title: document.title ?? '',
    summary: document.summary ?? '',
    documentNumber: document.documentNumber ?? document.document_number ?? '',
    year: document.year ? String(document.year) : '',
    quarter: numberFrom(document.quarter, 0) || null,
    publishedOn: document.publishedOn ?? document.published_on ?? null,
    updatedAt: document.updatedAt ?? document.updated_at ?? null,
    isFeatured: Boolean(document.isFeatured ?? document.is_featured),
    category: document.category ?? null,
    file: document.file ? {
      ...document.file,
      url: downloadUrl(document.file.url),
      mimeType: document.file.mimeType ?? document.file.mime_type ?? '',
      extension: String(document.file.extension ?? '').toUpperCase(),
      size: numberFrom(document.file.size, 0),
    } : null,
  }
}

function normalizeResponse(payload, requested) {
  const source = payload?.data && !Array.isArray(payload.data) ? payload.data : payload
  const items = (Array.isArray(source?.items) ? source.items : []).map(normalizeDocument).filter(Boolean)
  const limit = Math.max(1, numberFrom(source?.limit ?? requested.limit, 20))
  const total = Math.max(0, numberFrom(source?.total, items.length))
  const rawConfig = source?.pageConfig ?? source?.page_config ?? {}

  return {
    items,
    categories: Array.isArray(source?.categories) ? source.categories : [],
    years: (Array.isArray(source?.years) ? source.years : []).map(String),
    pageConfig: {
      ...DEFAULT_INVESTOR_PAGE_CONFIG,
      ...rawConfig,
      seo: { ...DEFAULT_INVESTOR_PAGE_CONFIG.seo, ...(rawConfig.seo ?? {}) },
    },
    total,
    page: Math.max(1, numberFrom(source?.page ?? requested.page, 1)),
    limit,
    lastPage: Math.max(1, numberFrom(source?.lastPage ?? source?.last_page, Math.ceil(total / limit) || 1)),
  }
}

export const investorsService = {
  async getDocuments({ locale = 'vi', page = 1, limit, category, year, search, sort = 'newest' } = {}, { signal } = {}) {
    const params = cleanParams({ locale, page, limit, category, year, search, sort })
    const response = await api.get('/investors/documents', { params, signal })
    return normalizeResponse(response.data, params)
  },
}
