/**
 * news.service.js
 * Phase 1: Returns static news data
 * Phase 2: Swap to CMS API calls
 */
import { NEWS_DATA } from '@data/news'

export const newsService = {
  getAll: ({ page = 1, limit = 12, category } = {}) => {
    let results = [...NEWS_DATA].sort((a, b) => new Date(b.date) - new Date(a.date))
    if (category) results = results.filter(n => n.category === category)
    const total = results.length
    const paginated = results.slice((page - 1) * limit, page * limit)
    return Promise.resolve({ items: paginated, total, page, limit })
  },

  getBySlug: (slug) => {
    const item = NEWS_DATA.find(n => n.slug === slug)
    if (!item) return Promise.reject(new Error(News not found: ))
    return Promise.resolve(item)
  },

  getFeatured: (limit = 3) => {
    return Promise.resolve(
      [...NEWS_DATA].sort((a, b) => new Date(b.date) - new Date(a.date)).slice(0, limit)
    )
  },
}
