import { useEffect, useState } from 'react'
import { BREAKPOINTS } from '@utils/constants'

/**
 * useMediaQuery — Returns true if the media query matches.
 *
 * @param {string} query - CSS media query string
 * @returns {boolean}
 *
 * Usage:
 *   const isDesktop = useMediaQuery('(min-width: 1024px)')
 *   const isMobile = useMediaQuery((max-width: px))
 */
export function useMediaQuery(query) {
  const [matches, setMatches] = useState(
    () => window.matchMedia(query).matches
  )

  useEffect(() => {
    const mql = window.matchMedia(query)
    const handler = (e) => setMatches(e.matches)

    mql.addEventListener('change', handler)
    return () => mql.removeEventListener('change', handler)
  }, [query])

  return matches
}

// Convenience hooks for common breakpoints
export const useIsMobile  = () => useMediaQuery((max-width: px))
export const useIsTablet  = () => useMediaQuery((min-width: px) and (max-width: px))
export const useIsDesktop = () => useMediaQuery((min-width: px))
