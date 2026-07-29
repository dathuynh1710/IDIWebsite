/**
 * products.service.js
 * Phase 1: Returns data from src/data/products.js
 * Phase 2: Swap to api.get('/products') calls
 */
import { PRODUCTS_DATA } from '@data/products'

export const productsService = {
  /** Get all products, with optional filter params */
  getAll: ({ category, certification, market } = {}) => {
    let results = [...PRODUCTS_DATA]
    if (category)      results = results.filter(p => p.category === category)
    if (certification) results = results.filter(p => p.certifications?.includes(certification))
    if (market)        results = results.filter(p => p.markets?.includes(market))
    return Promise.resolve(results)
  },

  /** Get a single product by URL slug */
  getBySlug: (slug) => {
    const product = PRODUCTS_DATA.find(p => p.slug === slug)
    if (!product) return Promise.reject(new Error(Product not found: ))
    return Promise.resolve(product)
  },

  /** Get featured products for homepage */
  getFeatured: () => {
    return Promise.resolve(PRODUCTS_DATA.filter(p => p.featured))
  },

  /** Get products in same category (for "Related Products") */
  getRelated: (slug, limit = 3) => {
    const product = PRODUCTS_DATA.find(p => p.slug === slug)
    if (!product) return Promise.resolve([])
    return Promise.resolve(
      PRODUCTS_DATA.filter(p => p.category === product.category && p.slug !== slug).slice(0, limit)
    )
  },
}
