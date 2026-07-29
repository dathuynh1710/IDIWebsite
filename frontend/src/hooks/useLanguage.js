/**
 * useLanguage — Convenience re-export of LanguageContext hook.
 * Prevents deep import paths in components.
 *
 * Usage:
 *   import { useLanguage } from '@hooks/useLanguage'
 *   const { t, language, setLanguage } = useLanguage()
 */
export { useLanguageContext as useLanguage } from '@context/LanguageContext'
