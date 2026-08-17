import api from './api'

const flattenCatalog = catalog => catalog.categories.flatMap(category => (
  category.products.map(product => ({ ...product, category }))
))

export const productsService = {
  async getCatalog({ locale = 'vi', category } = {}) {
    const response = await api.get('/products', { params: { locale, category } })
    return response.data
  },

  async getAll(filters = {}) {
    return flattenCatalog(await this.getCatalog(filters))
  },

  async getBySlug(slug, { locale = 'vi' } = {}) {
    const response = await api.get(`/products/${encodeURIComponent(slug)}`, { params: { locale } })
    return response.data.data
  },

  async getFeatured({ locale = 'vi' } = {}) {
    const products = await this.getAll({ locale })
    return products.filter(product => product.isFeatured)
  },

  async getRelated(slug, limit = 3, { locale = 'vi' } = {}) {
    const products = await this.getAll({ locale })
    const product = products.find(item => item.slug === slug)
    if (!product) return []

    return products
      .filter(item => item.category.id === product.category.id && item.slug !== slug)
      .slice(0, limit)
  },
}
