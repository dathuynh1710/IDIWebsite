import { usePublicRouting } from '@context/PublicRoutingContext'
import { createContext, useContext, useEffect, useState } from 'react'
import { useLanguage } from '@hooks/useLanguage'
import { useAboutRouting } from '@context/AboutRoutingContext'
import { useProductCategories } from '@hooks/useProductCategories'
import { menuService } from '@services/menu.service'
import { aboutApiLocale } from '@utils/aboutRoutes'
import { NAV_ITEMS, FOOTER_LINKS, localizedNavItems, withProductCategories } from '@data/navigation'

const Context = createContext(null)
export const useMenu = location => useContext(Context)[location]

export default function MenuProvider({ children }) {
  const { language, t } = useLanguage()
  const routing = usePublicRouting()
  const localizePaths = items => items.map(item => ({ ...item, href: routing ? routing.resolveLink(item.href) : item.href, children: localizePaths(item.children || []) }))
  const { resolveLink } = useAboutRouting()
  const categories = useProductCategories(language)
  const locale = aboutApiLocale(language)
  const [responses, setResponses] = useState({})
  const [version, setVersion] = useState(0)
  useEffect(() => {
    let active = true
    for (const location of ['main', 'footer']) {
      menuService.getMenu(location, locale).then(items => {
        if (active) setResponses(old => ({ ...old, [location]: { locale, status: 'ready', managed: true, items } }))
      }).catch(error => {
        if (active) setResponses(old => ({ ...old, [location]: {
          locale,
          // Never resurrect hardcoded items after an admin-managed menu was loaded.
          status: error?.response?.status === 404 && !old[location]?.managed ? 'legacy' : 'error',
          managed: old[location]?.managed || old[location]?.status === 'ready',
          items: old[location]?.locale === locale ? old[location]?.items : undefined,
        } }))
      })
    }
    return () => { active = false }
  }, [locale, version])

  const localize = items => localizedNavItems(items, t).map(item => ({
    ...item, href: resolveLink(item.href), children: item.children ? localize(item.children) : [],
  }))
  const mainFallback = localize(withProductCategories(NAV_ITEMS, categories))
  const footerFallback = Object.entries(FOOTER_LINKS).map(([id, col]) => ({
    id, label: t(col.titleKey), href: null,
    children: id === 'products' && categories.length
      ? categories.map(category => ({ id: category.id, label: category.name, href: `/products?category=${encodeURIComponent(category.slug)}` }))
      : localize(col.links),
  }))
  const value = Object.fromEntries(['main', 'footer'].map(location => {
    const response = responses[location]?.locale === locale ? responses[location] : null
    return [location, {
      status: response?.status || 'loading',
      items: localizePaths(response?.status === 'legacy' ? (location === 'main' ? mainFallback : footerFallback)
        : response?.items ?? [{ id: 'home', label: t('nav.home'), href: '/' }]),
      retry: () => setVersion(value => value + 1),
    }]
  }))
  return <Context.Provider value={value}>{children}</Context.Provider>
}
