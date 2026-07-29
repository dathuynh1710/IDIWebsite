import { createContext, useContext, useState, useCallback } from 'react'

// ─────────────────────────────────────────────────────────────
// Context definition
// ─────────────────────────────────────────────────────────────
const UIContext = createContext(null)

// ─────────────────────────────────────────────────────────────
// Provider
// ─────────────────────────────────────────────────────────────

/**
 * UIProvider — Manages global UI state that multiple components need to share.
 *
 * State managed here:
 *   - mobileMenuOpen: boolean — controls mobile menu drawer
 *   - activeModal: string | null — which modal is open (if any)
 *   - navbarScrolled: boolean — Navbar has scrolled past threshold
 *
 * Rule: Only truly "global" UI state lives here.
 * Local component state (e.g., tab selection, accordion open) stays local.
 */
export function UIProvider({ children }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const [activeModal, setActiveModal]       = useState(null)
  const [navbarScrolled, setNavbarScrolled] = useState(false)

  const openMobileMenu  = useCallback(() => setMobileMenuOpen(true), [])
  const closeMobileMenu = useCallback(() => setMobileMenuOpen(false), [])
  const toggleMobileMenu = useCallback(() => setMobileMenuOpen(prev => !prev), [])

  const openModal  = useCallback((modalId) => setActiveModal(modalId), [])
  const closeModal = useCallback(() => setActiveModal(null), [])

  return (
    <UIContext.Provider value={{
      mobileMenuOpen,
      openMobileMenu,
      closeMobileMenu,
      toggleMobileMenu,
      activeModal,
      openModal,
      closeModal,
      navbarScrolled,
      setNavbarScrolled,
    }}>
      {/* Prevent body scroll when mobile menu is open */}
      {mobileMenuOpen && (
        <style>{`body { overflow: hidden; }`}</style>
      )}
      {children}
    </UIContext.Provider>
  )
}

// ─────────────────────────────────────────────────────────────
// Custom hook
// ─────────────────────────────────────────────────────────────
export function useUIContext() {
  const ctx = useContext(UIContext)
  if (!ctx) {
    throw new Error('useUIContext must be used within <UIProvider>')
  }
  return ctx
}

export default UIContext
