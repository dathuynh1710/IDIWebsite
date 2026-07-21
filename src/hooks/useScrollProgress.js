import { useEffect, useState } from 'react'

/**
 * useScrollProgress — Returns the current scroll percentage (0–100).
 * Used for reading progress bars on news/article pages.
 *
 * @returns {number} 0-100
 */
export function useScrollProgress() {
  const [progress, setProgress] = useState(0)

  useEffect(() => {
    const updateProgress = () => {
      const scrollTop    = window.scrollY
      const docHeight    = document.documentElement.scrollHeight - window.innerHeight
      const percentage   = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0
      setProgress(Math.min(100, Math.round(percentage)))
    }

    window.addEventListener('scroll', updateProgress, { passive: true })
    return () => window.removeEventListener('scroll', updateProgress)
  }, [])

  return progress
}
