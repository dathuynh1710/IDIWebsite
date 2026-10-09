import { createContext, useContext, useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router'
import api from '@services/api'
import { useLanguage } from '@hooks/useLanguage'
import { pathLocale, uiLocale, publicLink, routeEntry } from '@utils/publicRoutes'
import PageLoader from '@components/common/PageLoader'
import NotFoundPage from '@pages/errors/NotFoundPage'

const Context = createContext(null)
export const usePublicRouting = () => useContext(Context)
export default function PublicRoutingProvider({ children }) {
  const location = useLocation()
  const navigate = useNavigate()
  const { language, setLanguage } = useLanguage()
  const [data, setData] = useState(null)
  const [failed, setFailed] = useState(false)
  const locale = pathLocale(location.pathname)
  useEffect(() => {
    if (locale && uiLocale(locale) !== language) setLanguage(uiLocale(locale))
  }, [locale, language, setLanguage])
  useEffect(() => {
    let active = true
    api.get('/public-routes').then(response => { if (active) setData(response.data) }).catch(() => { if (active) setFailed(true) })
    return () => { active = false }
  }, [])
  const entries = data?.entries || []
  const entry = data ? entries.find(item => Object.values(item.paths).includes(location.pathname)) : routeEntry(location.pathname)
  let target = location.pathname
  const seen = new Set()
  const normalized = location.pathname.replace(/\/+$/, '') || '/'
  if (normalized !== location.pathname && entries.some(item => Object.values(item.paths).includes(normalized))) target = normalized
  while (data?.redirects?.[target] && !seen.has(target)) { seen.add(target); target = data.redirects[target] }
  if (!routeEntry(target, entries) && target !== location.pathname) target = location.pathname
  if (target === location.pathname && !locale) target = publicLink(location.pathname + location.search, 'vi', entries) || location.pathname
  if (entry && location.search && location.pathname.endsWith('/' + ({ vi: 'san-pham', en: 'products', zh: 'chanpin' }[locale]))) target = publicLink(location.pathname + location.search, locale, entries) || location.pathname
  useEffect(() => {
    if (data && target !== location.pathname && target !== location.pathname + location.search) {
      const url = new URL(target, window.location.origin)
      navigate(url.pathname + (url.search || location.search) + location.hash, { replace: true })
    }
  }, [data, target, location.pathname, location.search, location.hash, navigate])
  const value = { entries, entry, resolveLink: href => publicLink(href, locale || language, entries) }
  if (!data && !failed) return <PageLoader />
  if (locale && uiLocale(locale) !== language) return <PageLoader />
  if (locale && data && !entry && target === location.pathname) return <NotFoundPage />
  if (failed) return <p role="alert">Unable to load page routes. <button onClick={() => window.location.reload()}>Retry</button></p>
  return <Context.Provider value={value}>{children}</Context.Provider>
}
