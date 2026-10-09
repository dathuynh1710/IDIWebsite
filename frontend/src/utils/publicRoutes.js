import staticRoutes from '../../../shared/public-routes.json'
export { staticRoutes }
export const apiLocale = locale => locale === 'zh-CN' ? 'zh' : locale
export const pathLocale = path => path.match(/^\/(vi|en|zh)(?:\/|$)/)?.[1]
export const uiLocale = locale => locale === 'zh' ? 'zh-CN' : locale
export function routeEntry(path, entries = []) {
  return entries.find(entry => Object.values(entry.paths).includes(path))
    || Object.entries(staticRoutes).map(([legacy, paths]) => ({ legacy, paths, name: 'static' })).find(entry => Object.values(entry.paths).includes(path) || entry.legacy === path)
}
export function publicLink(href, locale, entries = []) {
  if (typeof href !== 'string' || !href.startsWith('/') || href.startsWith('//')) return href
  locale = apiLocale(locale)
  const url = new URL(href, 'https://local.invalid')
  const path = url.pathname
  let entry = routeEntry(path, entries)
  if (entry?.name === 'static' && entries.length && !entries.some(item => item.legacy === entry.legacy)) return null
  const about = { '/about': 'ABOUT_MESSAGE', '/about/story': 'ABOUT_HISTORY', '/about/values': 'ABOUT_VALUES' }
  if (about[path]) entry = entries.find(item => item.code === about[path])
  if ((path === '/products' || Object.values(staticRoutes['/products']).includes(path)) && url.searchParams.has('category')) {
    const slug = url.searchParams.get('category')
    const category = entries.find(item => item.name === 'product-categories.show' && Object.values(item.slugs || {}).includes(slug))
    if (category?.paths[locale]) { entry = category; url.searchParams.delete('category') }
  }
  if (!entry) entry = entries.find(item => item.group && Object.values(item.slugs || {}).some(slug => path === `/${item.group}/${slug}`))
  if (entry) return entry.paths[locale] ? entry.paths[locale] + url.search + url.hash : null
  if (/^\/(products|news|recipes|careers)\//.test(path) || about[path]) return null
  return href
}
