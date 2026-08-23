import api from './api'

function mediaUrl(value) {
  if (!value || !value.startsWith('/') || value.startsWith('//')) return value || ''

  try {
    const apiBase = new URL(api.defaults.baseURL || '/api', window.location.origin)
    return new URL(value, apiBase.origin).href
  } catch {
    return value
  }
}

function normalizeRecipe(recipe) {
  return {
    ...recipe,
    image: recipe.image ? { ...recipe.image, url: mediaUrl(recipe.image.url) } : null,
    videoUrl: mediaUrl(recipe.videoUrl),
  }
}

export const recipesService = {
  async getAll({ locale = 'vi', page = 1, limit } = {}, config = {}) {
    const response = await api.get('/recipes', { ...config, params: { locale, page, limit } })
    return {
      ...response.data,
      items: (response.data.items ?? []).map(normalizeRecipe),
    }
  },

  async getBySlug(slug, locale = 'vi', config = {}) {
    const response = await api.get(`/recipes/${encodeURIComponent(slug)}`, {
      ...config,
      params: { locale },
    })
    return {
      data: normalizeRecipe(response.data.data),
      pageConfig: response.data.pageConfig,
    }
  },
}
