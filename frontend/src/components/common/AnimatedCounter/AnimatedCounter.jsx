import { useAnimatedCounter } from '@hooks/useAnimatedCounter'

/**
 * AnimatedCounter — Counts from 0 → target when it enters the viewport.
 * Uses eased cubic animation from useAnimatedCounter hook.
 *
 * @param {number} target - Number to count to
 * @param {string} prefix - e.g. '$', '+'
 * @param {string} suffix - e.g. '+', 'MT', '%'
 * @param {number} duration - Animation duration in ms
 * @param {boolean} compact - Show compact form e.g. 100000 → '100K'
 */
export default function AnimatedCounter({
  target,
  prefix = '',
  suffix = '',
  duration = 2000,
  compact = false,
}) {
  const { ref, count } = useAnimatedCounter(target, duration)

  const display = compact
    ? count >= 1000 ? `${(count / 1000).toFixed(0)}K` : count
    : count.toLocaleString()

  return (
    <span ref={ref}>
      {prefix}{display}{suffix}
    </span>
  )
}
