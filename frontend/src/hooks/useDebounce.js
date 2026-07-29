import { useEffect, useState } from 'react'

/**
 * useDebounce — Delays updating a value until after a pause in changes.
 * Used for search/filter inputs to avoid firing on every keystroke.
 *
 * @param {*} value - The value to debounce
 * @param {number} delay - Milliseconds to wait
 * @returns {*} Debounced value
 *
 * Usage:
 *   const [query, setQuery] = useState('')
 *   const debouncedQuery = useDebounce(query, 300)
 *   useEffect(() => { search(debouncedQuery) }, [debouncedQuery])
 */
export function useDebounce(value, delay = 300) {
  const [debouncedValue, setDebouncedValue] = useState(value)

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedValue(value), delay)
    return () => clearTimeout(timer)
  }, [value, delay])

  return debouncedValue
}
