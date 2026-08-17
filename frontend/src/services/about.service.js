import api from './api'

export const aboutService = {
  async getPages({ locale = 'vi' } = {}) {
    const response = await api.get('/about', { params: { locale } })
    return response.data
  },

  async getPage(identifier, { locale = 'vi' } = {}) {
    const response = await api.get(`/about/${encodeURIComponent(identifier)}`, {
      params: { locale },
    })
    return response.data.data
  },
}
