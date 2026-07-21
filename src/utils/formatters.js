/**
 * formatters.js — Pure formatting functions.
 * No side effects. All functions are deterministic.
 */

// ── Date Formatting

/**
 * Formats an ISO date string or Date object.
 * @param {string|Date} date
 * @param {string} locale - e.g. 'en-US', 'vi-VN', 'zh-CN'
 * @param {Intl.DateTimeFormatOptions} options
 * @returns {string}
 *
 * Usage:
 *   formatDate('2025-04-15')             → "April 15, 2025"
 *   formatDate('2025-04-15', 'vi-VN')   → "15 tháng 4, 2025"
 */
export function formatDate(date, locale = 'en-US', options = {}) {
  const defaultOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    ...options,
  }
  return new Intl.DateTimeFormat(locale, defaultOptions).format(new Date(date))
}

/**
 * Short date format: "Apr 15, 2025"
 */
export function formatDateShort(date, locale = 'en-US') {
  return formatDate(date, locale, { year: 'numeric', month: 'short', day: 'numeric' })
}

/**
 * ISO string to "YYYY-MM-DD" (for datetime attributes)
 */
export function formatDateISO(date) {
  return new Date(date).toISOString().split('T')[0]
}


// ── Number Formatting

/**
 * Formats large numbers with locale-appropriate separators.
 * @param {number} num
 * @param {string} locale
 * @returns {string}
 *
 * Usage:
 *   formatNumber(100000)       → "100,000"
 *   formatNumber(100000, 'vi') → "100.000"
 */
export function formatNumber(num, locale = 'en-US') {
  return new Intl.NumberFormat(locale).format(num)
}

/**
 * Compact large numbers: 100000 → "100K", 1000000 → "1M"
 */
export function formatNumberCompact(num, locale = 'en-US') {
  return new Intl.NumberFormat(locale, {
    notation: 'compact',
    maximumFractionDigits: 1,
  }).format(num)
}

/**
 * Format metric tonnes with unit
 */
export function formatMT(num) {
  return `${formatNumber(num)} MT`
}


// ── String Formatting

/**
 * Truncates text to maxLength, appending "..." if truncated.
 * @param {string} text
 * @param {number} maxLength
 * @returns {string}
 */
export function truncate(text, maxLength = 120) {
  if (!text || text.length <= maxLength) return text
  return text.slice(0, maxLength).trimEnd() + '...'
}

/**
 * Capitalizes first letter of each word.
 */
export function titleCase(str) {
  return str.toLowerCase().replace(/\b\w/g, (char) => char.toUpperCase())
}

/**
 * Formats a phone number for display.
 * Input: "+842773680383" or "0277 368 0383"
 * Output: "+84 277 368 0383"
 * (Simple display format — not E.164 strict)
 */
export function formatPhone(phone) {
  if (!phone) return ''
  return phone.replace(/\s+/g, ' ').trim()
}


// ── URL / File Helpers

/**
 * Returns human-readable file size.
 * @param {number} bytes
 * @returns {string}
 */
export function formatFileSize(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

/**
 * Returns file extension from filename.
 * "spec-sheet.pdf" → "PDF"
 */
export function formatFileExt(filename) {
  return filename.split('.').pop().toUpperCase()
}
