import { useEffect } from 'react'

/**
 * PageHead — Sets document title and meta description for SEO.
 * Uses a simple useEffect approach (no external library needed for Phase 1).
 *
 * @param {string} title       - Full page title (shown in browser tab & search results)
 * @param {string} description - Meta description for search engines (150–160 chars ideal)
 */
export default function PageHead({ title, description, keywords }) {
  useEffect(() => {
    if (title) {
      document.title = title
    }
    if (description) {
      let meta = document.querySelector('meta[name="description"]')
      if (!meta) {
        meta = document.createElement('meta')
        meta.setAttribute('name', 'description')
        document.head.appendChild(meta)
      }
      meta.setAttribute('content', description)
    }
    const keywordsMeta = document.querySelector('meta[name="keywords"]')
    if (keywords) {
      let meta = keywordsMeta
      if (!meta) {
        meta = document.createElement('meta')
        meta.setAttribute('name', 'keywords')
        document.head.appendChild(meta)
      }
      meta.setAttribute('content', keywords)
    } else if (keywordsMeta) {
      keywordsMeta.remove()
    }
  }, [title, description, keywords])

  return null
}
