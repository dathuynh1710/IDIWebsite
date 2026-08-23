import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { DEFAULT_LANGUAGE, LANGUAGES } from '@utils/constants'
import { TRANSLATIONS } from '@/i18n/translations'

export const LANGUAGE_STORAGE_KEY = 'idi_lang'
const supportedLanguages = Object.values(LANGUAGES)
const LanguageContext = createContext(null)

export function getStoredLanguage() {
  if (typeof window === 'undefined') return DEFAULT_LANGUAGE
  const stored = window.localStorage.getItem(LANGUAGE_STORAGE_KEY)
  return supportedLanguages.includes(stored) ? stored : DEFAULT_LANGUAGE
}

function interpolate(value, variables) {
  if (!variables || typeof value !== 'string') return value
  return value.replace(/\{\{(\w+)\}\}/g, (_, name) => variables[name] ?? '')
}

export function LanguageProvider({ children }) {
  const [language, setLanguageState] = useState(getStoredLanguage)

  useEffect(() => {
    document.documentElement.lang = language
    window.localStorage.setItem(LANGUAGE_STORAGE_KEY, language)
  }, [language])

  const setLanguage = useCallback((nextLanguage) => {
    if (!supportedLanguages.includes(nextLanguage)) {
      console.warn(`[LanguageContext] Unsupported language: "${nextLanguage}"`)
      return
    }
    window.localStorage.setItem(LANGUAGE_STORAGE_KEY, nextLanguage)
    document.documentElement.lang = nextLanguage
    setLanguageState(nextLanguage)
  }, [])

  const t = useCallback((key, fallbackOrVariables, variables) => {
    const value = key.split('.').reduce((result, part) => result?.[part], TRANSLATIONS[language])
    const fallback = typeof fallbackOrVariables === 'string' ? fallbackOrVariables : key
    const interpolationValues = typeof fallbackOrVariables === 'object' ? fallbackOrVariables : variables
    return interpolate(value ?? fallback, interpolationValues)
  }, [language])

  const value = useMemo(() => ({ language, setLanguage, t, isLoading: false }), [language, setLanguage, t])
  return <LanguageContext.Provider value={value}>{children}</LanguageContext.Provider>
}

export function useLanguageContext() {
  const context = useContext(LanguageContext)
  if (!context) throw new Error('useLanguageContext must be used within <LanguageProvider>')
  return context
}

export default LanguageContext
