import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * cn — Tailwind-safe className merger.
 *
 * Combines clsx (conditional classes) + twMerge (deduplicates Tailwind classes).
 *
 * Without twMerge: cn('px-4', 'px-6') → 'px-4 px-6' (both applied — bug!)
 * With twMerge:    cn('px-4', 'px-6') → 'px-6'      (last wins — correct!)
 *
 * Usage:
 *   cn('base-class', condition && 'conditional-class', props.className)
 *   cn('text-white bg-ocean-deep', variant === 'ghost' && 'bg-transparent')
 */
export function cn(...inputs) {
  return twMerge(clsx(inputs))
}
