import { useIntersectionObserver } from '@hooks/useIntersectionObserver'
import { cn } from '@utils/cn'

/**
 * RevealOnScroll — Wraps children in a scroll-triggered reveal animation.
 * Uses IntersectionObserver — fires once, never resets.
 *
 * @param {string} direction - 'up' (default) | 'left' | 'right' | 'none'
 * @param {number} delay - Animation delay in ms (for staggering siblings)
 * @param {number} threshold - 0–1, how much of element must be visible to trigger
 */
export default function RevealOnScroll({
  children,
  className,
  direction = 'up',
  delay = 0,
  threshold = 0.12,
  as: Tag = 'div',
}) {
  const { ref, hasIntersected } = useIntersectionObserver({ threshold })

  const hiddenClass = {
    up:    'opacity-0 translate-y-8',
    left:  'opacity-0 -translate-x-8',
    right: 'opacity-0 translate-x-8',
    none:  'opacity-0',
  }[direction]

  return (
    <Tag
      ref={ref}
      className={cn(
        'transition-all duration-700 ease-out',
        hasIntersected ? 'opacity-100 translate-y-0 translate-x-0' : hiddenClass,
        className,
      )}
      style={{ transitionDelay: hasIntersected ? `${delay}ms` : '0ms' }}
    >
      {children}
    </Tag>
  )
}
