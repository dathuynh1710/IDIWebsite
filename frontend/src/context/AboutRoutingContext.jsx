import { createContext, useContext, useEffect, useState } from 'react'
import { useLocation } from 'react-router'
import { useLanguage } from '@hooks/useLanguage'
import { aboutService } from '@services/about.service'
import { aboutLink, aboutLocale, aboutApiLocale, aboutUiLanguage } from '@utils/aboutRoutes'

const Context = createContext(null)
export const useAboutRouting = () => useContext(Context)

export default function AboutRoutingProvider({ children }) {
  const { pathname } = useLocation()
  const { language, setLanguage } = useLanguage()
  const locale = aboutLocale(pathname) || aboutApiLocale(language)
  const [current, setCurrent] = useState(null)
  const [listing, setListing] = useState(null)
  useEffect(() => {
    if (aboutUiLanguage(locale) !== language) setLanguage(aboutUiLanguage(locale))
  }, [locale, language, setLanguage])
  useEffect(() => {
    let active = true
    aboutService.getPages({ locale }).then(data => {
      if (active) setListing({ locale, pages: data.items || [] })
    }).catch(() => { if (active) setListing(null) })
    return () => { active = false }
  }, [locale])
  const pages = listing?.locale === locale ? listing.pages : []
  return <Context.Provider value={{
    page: current?.pathname === pathname ? current.page : null,
    setCurrent,
    resolveLink: href => aboutLink(href, pages, locale),
  }}>{children}</Context.Provider>
}
