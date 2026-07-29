import { createContext, useContext, useState, useCallback } from 'react'
import { LANGUAGES, DEFAULT_LANGUAGE } from '@utils/constants'

// ─────────────────────────────────────────────────────────────
// Context definition
// ─────────────────────────────────────────────────────────────
const LanguageContext = createContext(null)

// ─────────────────────────────────────────────────────────────
// Provider
// ─────────────────────────────────────────────────────────────

/**
 * LanguageProvider — Manages active language and provides t() translation function.
 *
 * Phase 1: Translations are loaded from public/locales/*.json via fetch.
 * Phase 2: Can swap to i18next or react-i18next if complexity grows.
 *
 * Context value:
 *   - language: 'en' | 'vi' | 'zh'
 *   - setLanguage: (lang: string) => void
 *   - t: (key: string, fallback?: string) => string
 */
export function LanguageProvider({ children }) {
  const [language, setLanguage] = useState(() => {
    // Persist language preference in localStorage
    return localStorage.getItem('idi_lang') ?? DEFAULT_LANGUAGE
  })

  const [translations, setTranslations] = useState({})
  const [isLoading, setIsLoading] = useState(false)

  // Load translations JSON for the selected language
  const loadTranslations = useCallback(async (lang) => {
    if (translations[lang]) return // Already loaded

    setIsLoading(true)
    try {
      const response = await fetch(`/locales/${lang}.json`)
      if (!response.ok) throw new Error(`Failed to load ${lang} translations`)
      const data = await response.json()
      setTranslations(prev => ({ ...prev, [lang]: data }))
    } catch (error) {
      console.warn(`[LanguageContext] Could not load translations for "${lang}":`, error)
    } finally {
      setIsLoading(false)
    }
  }, [translations])

  // Change language + persist preference
  const handleSetLanguage = useCallback(async (lang) => {
    if (!Object.values(LANGUAGES).includes(lang)) {
      console.warn(`[LanguageContext] Unsupported language: "${lang}"`)
      return
    }
    await loadTranslations(lang)
    setLanguage(lang)
    localStorage.setItem('idi_lang', lang)
    // Update <html lang> attribute for accessibility + SEO
    document.documentElement.setAttribute('lang', lang)
  }, [loadTranslations])

  /**
   * t() — Translation lookup function.
   * Supports dot-notation keys: t('nav.products') → translations.nav.products
   * Falls back to key string if translation missing.
   *
   * @param {string} key - Dot-notation translation key
   * @param {string} fallback - Optional fallback string
   */
  const t = useCallback((key, fallback = key) => {
    const dict = translations[language]
    if (!dict) return fallback

    const value = key.split('.').reduce((obj, k) => obj?.[k], dict)
    return value ?? fallback
  }, [language, translations])

  return (
    <LanguageContext.Provider value={{ language, setLanguage: handleSetLanguage, t, isLoading }}>
      {children}
    </LanguageContext.Provider>
  )
}

// ─────────────────────────────────────────────────────────────
// Custom hook — enforces provider usage
// ─────────────────────────────────────────────────────────────
export function useLanguageContext() {
  const ctx = useContext(LanguageContext)
  if (!ctx) {
    throw new Error('useLanguageContext must be used within <LanguageProvider>')
  }
  return ctx
}

export default LanguageContext
