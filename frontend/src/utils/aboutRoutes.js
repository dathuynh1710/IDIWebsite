export const aboutApiLocale = locale => locale === 'zh-CN' ? 'zh' : locale
export const aboutUiLanguage = locale => locale === 'zh' ? 'zh-CN' : locale
export const ABOUT_PATTERNS = { vi: '/vi/gioi-thieu', en: '/en/about', zh: '/zh/guanyu' }
export const LEGACY_ABOUT = {
  '/about': ['ABOUT_MESSAGE', 'about'],
  '/about/story': ['ABOUT_HISTORY', 'about-history'],
  '/about/values': ['ABOUT_VALUES', 'about-values'],
}
export function aboutLocale(pathname) {
  return Object.keys(ABOUT_PATTERNS).find(locale => pathname.startsWith(`${ABOUT_PATTERNS[locale]}/`))
}
export function localizedAboutPath(page, locale) {
  locale = aboutApiLocale(locale)
  const path = page?.localizedPaths?.[locale]
  return typeof path === 'string' && path.startsWith(`${ABOUT_PATTERNS[locale]}/`) ? path : null
}
export function aboutLink(href, pages, locale) {
  const identity = LEGACY_ABOUT[href]
  if (!identity) return href
  const page = pages.find(page => page.code === identity[0])
    || pages.find(page => page.template === identity[1])
  return localizedAboutPath(page, locale) || href
}
