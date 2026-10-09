import api from './api'

export const menuService = {
  async getMenu(location, locale) {
    const response = await api.get(`/menus/${encodeURIComponent(location)}`, { params: { locale } })
    if (!Array.isArray(response.data.items)) throw new Error('Invalid menu response')
    return response.data.items
  },
}
