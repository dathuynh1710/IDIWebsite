import { LanguageProvider } from '@context/LanguageContext'
import { UIProvider } from '@context/UIContext'

/**
 * Providers — Composes all React context providers.
 *
 * Order matters: outer providers are available to inner ones.
 * Current order:
 *   UIProvider (no deps)
 *   └── LanguageProvider (may read UI state in future)
 *         └── children (all app routes)
 *
 * Adding a new global context? Add it here, NOT in App.jsx or layouts.
 */
export default function Providers({ children }) {
  return (
    <UIProvider>
      <LanguageProvider>
        {children}
      </LanguageProvider>
    </UIProvider>
  )
}
