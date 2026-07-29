import { useEffect, useRef, useState } from 'react'
import { useIntersectionObserver } from './useIntersectionObserver'

/**
 * useAnimatedCounter — Counts from 0 to target when element enters viewport.
 *
 * @param {number} target - Final number to count to
 * @param {number} duration - Animation duration in ms
 * @returns {{ ref: React.RefObject, count: number }}
 *
 * Usage:
 *   const { ref, count } = useAnimatedCounter(100000, 2000)
 *   <span ref={ref}>{formatNumber(count)}</span>
 */
export function useAnimatedCounter(target, duration = 2000) {
  const { ref, hasIntersected } = useIntersectionObserver({ threshold: 0.5 })
  const [count, setCount] = useState(0)
  const rafRef = useRef(null)

  useEffect(() => {
    if (!hasIntersected) return

    const startTime = performance.now()
    const startValue = 0

    const tick = (currentTime) => {
      const elapsed  = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)
      // Ease out cubic
      const eased    = 1 - Math.pow(1 - progress, 3)
      setCount(Math.round(startValue + eased * (target - startValue)))

      if (progress < 1) {
        rafRef.current = requestAnimationFrame(tick)
      }
    }

    rafRef.current = requestAnimationFrame(tick)
    return () => cancelAnimationFrame(rafRef.current)
  }, [hasIntersected, target, duration])

  return { ref, count }
}
