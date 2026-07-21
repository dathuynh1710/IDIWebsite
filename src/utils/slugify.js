/**
 * slugify.js — Converts text to URL-safe slugs.
 * Handles Vietnamese diacritics (critical for IDI's content).
 */

// Vietnamese character map → ASCII equivalents
const VIETNAMESE_MAP = {
  'à':'a','á':'a','ả':'a','ã':'a','ạ':'a',
  'ă':'a','ắ':'a','ằ':'a','ẳ':'a','ẵ':'a','ặ':'a',
  'â':'a','ấ':'a','ầ':'a','ẩ':'a','ẫ':'a','ậ':'a',
  'đ':'d',
  'è':'e','é':'e','ẻ':'e','ẽ':'e','ẹ':'e',
  'ê':'e','ế':'e','ề':'e','ể':'e','ễ':'e','ệ':'e',
  'ì':'i','í':'i','ỉ':'i','ĩ':'i','ị':'i',
  'ò':'o','ó':'o','ỏ':'o','õ':'o','ọ':'o',
  'ô':'o','ố':'o','ồ':'o','ổ':'o','ỗ':'o','ộ':'o',
  'ơ':'o','ớ':'o','ờ':'o','ở':'o','ỡ':'o','ợ':'o',
  'ù':'u','ú':'u','ủ':'u','ũ':'u','ụ':'u',
  'ư':'u','ứ':'u','ừ':'u','ử':'u','ữ':'u','ự':'u',
  'ỳ':'y','ý':'y','ỷ':'y','ỹ':'y','ỵ':'y',
}

/**
 * Converts a string to a URL-safe slug.
 * @param {string} text
 * @returns {string}
 *
 * Usage:
 *   slugify('Cá Fillet Tạo Hình Sạch')  → 'ca-fillet-tao-hinh-sach'
 *   slugify('Pangasius Fillet Trim D')   → 'pangasius-fillet-trim-d'
 */
export function slugify(text) {
  if (!text) return ''

  return text
    .toLowerCase()
    .split('').map(char => VIETNAMESE_MAP[char] ?? char).join('') // Normalize Vietnamese
    .normalize('NFD')                    // Unicode decomposition
    .replace(/[\u0300-\u036f]/g, '')     // Remove remaining diacritics
    .replace(/[^a-z0-9\s-]/g, '')       // Remove non-alphanumeric (except space/dash)
    .trim()
    .replace(/\s+/g, '-')               // Spaces → hyphens
    .replace(/-+/g, '-')                // Collapse multiple hyphens
}

/**
 * Reverses a slug to a human-readable title (best-effort).
 * 'pangasius-fillet-trim-d' → 'Pangasius Fillet Trim D'
 */
export function deslugify(slug) {
  if (!slug) return ''
  return slug
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}
