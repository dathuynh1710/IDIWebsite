import { useEffect, useState } from 'react'
import { productsService } from '@services/products.service'

const cache = new Map()

function loadCategories(locale) {
  if (!cache.has(locale)) {
    cache.set(locale, productsService.getCatalog({ locale })
      .then(catalog => catalog.categories ?? [])
      .catch((error) => {
        cache.delete(locale)
        throw error
      }))
  }

  return cache.get(locale)
}

export function useProductCategories(locale) {
  const [categories, setCategories] = useState([])

  useEffect(() => {
    let active = true
    loadCategories(locale)
      .then(items => { if (active) setCategories(items) })
      .catch(() => { if (active) setCategories([]) })

    return () => { active = false }
  }, [locale])

  return categories
}
