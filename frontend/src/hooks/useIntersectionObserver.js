import { useEffect, useRef, useState } from 'react'

/**
 * useIntersectionObserver — Detects when an element enters the viewport.
 *
 * Used by RevealOnScroll component to trigger animations.
 *
 * @param {IntersectionObserverInit} options
 * @returns {{ ref: React.RefObject, isIntersecting: boolean, hasIntersected: boolean }}
 *
 * Usage:
 *   const { ref, hasIntersected } = useIntersectionObserver({ threshold: 0.15 })
 *   <div ref={ref} className={hasIntersected ? 'animate-fade-in-up' : 'opacity-0'} />
 */
export function useIntersectionObserver(options = {}) {
  const ref = useRef(null)
  const [isIntersecting, setIsIntersecting]   = useState(false)
  const [hasIntersected, setHasIntersected]   = useState(false)

  const { threshold = 0.1, rootMargin = '0px', root = null } = options

  useEffect(() => {
    const element = ref.current
    if (!element) return

    const observer = new IntersectionObserver(([entry]) => {
      setIsIntersecting(entry.isIntersecting)
      if (entry.isIntersecting) {
        setHasIntersected(true)  // Once true, stays true (one-shot animation)
        observer.unobserve(element) // Stop observing after first intersection
      }
    }, { threshold, rootMargin, root })

    observer.observe(element)
    return () => observer.disconnect()
  }, [threshold, rootMargin, root])

  return { ref, isIntersecting, hasIntersected }
}
