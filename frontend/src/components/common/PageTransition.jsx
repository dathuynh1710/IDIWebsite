import { useLayoutEffect, useRef } from 'react'
import { useLocation, useOutlet } from 'react-router'

const PAGE_TRANSITION_DURATION = 420
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)'

/**
 * Animates routed page content without remounting persistent nested layouts.
 * Query-string and hash updates are intentionally excluded from the trigger.
 */
export default function PageTransition() {
  const containerRef = useRef(null)
  const location = useLocation()
  const outlet = useOutlet()

  useLayoutEffect(() => {
    const container = containerRef.current
    const prefersReducedMotion = window.matchMedia?.(REDUCED_MOTION_QUERY).matches

    if (!container || prefersReducedMotion || typeof container.animate !== 'function') {
      return undefined
    }

    const animation = container.animate(
      [
        { opacity: 0, transform: 'translate3d(0, 14px, 0)' },
        { opacity: 1, transform: 'translate3d(0, 0, 0)' },
      ],
      {
        duration: PAGE_TRANSITION_DURATION,
        easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
      },
    )

    return () => animation.cancel()
  }, [location.pathname])

  return (
    <div ref={containerRef} className="page-route-transition">
      {outlet}
    </div>
  )
}
