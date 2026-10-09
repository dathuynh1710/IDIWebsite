import api from './api'

export const aboutService = {
  async getPages({ locale = 'vi' } = {}) {
    const response = await api.get('/about', { params: { locale } })
    return response.data
  },

  async getPage(identifier, { locale = 'vi', bySlug = false } = {}) {
    const response = await api.get(`/about/${encodeURIComponent(identifier)}`, {
      params: { locale, bySlug },
    })
    return response.data.data
  },
}
