import { SITE_NAME, SITE_TAGLINE, SITE_URL } from './constants'

/**
 * seo.js — Helpers for building page-level SEO metadata.
 * Used by the PageHead component on each page.
 */

/**
 * Builds the <title> tag string.
 * @param {string} pageTitle - Optional page-specific title
 * @returns {string}
 *
 * Usage:
 *   buildPageTitle()              → "IDI Seafood — Vietnam's Leading Pangasius Exporter"
 *   buildPageTitle('Products')   → "Products | IDI Seafood"
 *   buildPageTitle('Cá Fillet')  → "Cá Fillet | IDI Seafood"
 */
export function buildPageTitle(pageTitle = '') {
  if (!pageTitle) return `${SITE_NAME} — ${SITE_TAGLINE}`
  return `${pageTitle} | ${SITE_NAME}`
}

/**
 * Builds canonical URL for a given path.
 * @param {string} path - e.g. '/products/pangasius-fillet'
 * @returns {string}
 */
export function buildCanonicalUrl(path = '') {
  const cleanPath = path.startsWith('/') ? path : `/${path}`
  return `${SITE_URL}${cleanPath}`
}

/**
 * Default meta description if none provided.
 */
export const DEFAULT_DESCRIPTION =
  "IDI Seafood is Vietnam's leading pangasius exporter, supplying 50+ countries with ASC-certified, sustainable seafood products since 1997."

/**
 * Builds Open Graph meta object for a page.
 * @param {{ title, description, image, url }} params
 */
export function buildOGMeta({ title, description, image, url } = {}) {
  return {
    'og:title':       title       ?? buildPageTitle(),
    'og:description': description ?? DEFAULT_DESCRIPTION,
    'og:image':       image       ?? `${SITE_URL}/og-default.jpg`,
    'og:url':         url         ?? SITE_URL,
    'og:type':        'website',
    'og:site_name':   SITE_NAME,
  }
}

/**
 * Builds JSON-LD for a product page.
 * @param {{ name, description, image, sku, category }} product
 * @returns {object} JSON-LD object
 */
export function buildProductSchema({ name, description, image, sku, category }) {
  return {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name,
    description,
    image,
    sku,
    category,
    brand: {
      '@type': 'Brand',
      name: SITE_NAME,
    },
    manufacturer: {
      '@type': 'Organization',
      name: SITE_NAME,
      url: SITE_URL,
    },
  }
}

/**
 * Builds JSON-LD BreadcrumbList for structured data.
 * @param {Array<{ name: string, url: string }>} items
 */
export function buildBreadcrumbSchema(items) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: buildCanonicalUrl(item.url),
    })),
  }
}
