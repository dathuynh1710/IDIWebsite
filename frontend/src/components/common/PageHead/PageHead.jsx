import { useEffect } from 'react'

/**
 * PageHead — Sets document title and meta description for SEO.
 * Uses a simple useEffect approach (no external library needed for Phase 1).
 *
 * @param {string} title       - Full page title (shown in browser tab & search results)
 * @param {string} description - Meta description for search engines (150–160 chars ideal)
 */
export default function PageHead({
  title,
  description,
  keywords,
  canonical,
  image,
  type = 'website',
  ogTitle,
  ogDescription,
}) {
  useEffect(() => {
    const setMeta = (attribute, key, value) => {
      let meta = document.querySelector(`meta[${attribute}="${key}"]`)
      if (!value) {
        if (meta?.dataset.pageHead === 'true') meta.remove()
        return
      }
      if (!meta) {
        meta = document.createElement('meta')
        meta.setAttribute(attribute, key)
        document.head.appendChild(meta)
      }
      meta.dataset.pageHead = 'true'
      meta.setAttribute('content', value)
    }

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

    const resolvedTitle = ogTitle || title
    const resolvedDescription = ogDescription || description
    setMeta('property', 'og:title', resolvedTitle)
    setMeta('property', 'og:description', resolvedDescription)
    setMeta('property', 'og:type', type)
    setMeta('property', 'og:url', canonical)
    setMeta('property', 'og:image', image)
    setMeta('name', 'twitter:card', image ? 'summary_large_image' : 'summary')
    setMeta('name', 'twitter:title', resolvedTitle)
    setMeta('name', 'twitter:description', resolvedDescription)
    setMeta('name', 'twitter:image', image)

    let canonicalLink = document.querySelector('link[rel="canonical"]')
    if (canonical) {
      if (!canonicalLink) {
        canonicalLink = document.createElement('link')
        canonicalLink.setAttribute('rel', 'canonical')
        document.head.appendChild(canonicalLink)
      }
      canonicalLink.setAttribute('href', canonical)
    } else if (canonicalLink) {
      canonicalLink.remove()
    }
  }, [canonical, description, image, keywords, ogDescription, ogTitle, title, type])

  return null
}
