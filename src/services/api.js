import { API_BASE_URL } from '@utils/constants'

/**
 * api.js — Base HTTP client.
 *
 * Phase 1: Returns mocked data from src/data/*.js (services handle this).
 * Phase 2: Uncomment the fetch-based implementation below.
 *
 * All services import from this file for consistent error handling,
 * headers, and base URL management.
 */

// Default request options
const DEFAULT_OPTIONS = {
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
}

/**
 * Core fetch wrapper with error handling.
 * @param {string} endpoint - API endpoint (e.g. '/products')
 * @param {RequestInit} options - Fetch options
 * @returns {Promise<any>}
 */
async function request(endpoint, options = {}) {
  const url = `${API_BASE_URL}${endpoint}`

  const response = await fetch(url, {
    ...DEFAULT_OPTIONS,
    ...options,
    headers: {
      ...DEFAULT_OPTIONS.headers,
      ...options.headers,
    },
  })

  if (!response.ok) {
    const error = new Error(`HTTP Error ${response.status}: ${response.statusText}`)
    error.status = response.status
    error.endpoint = endpoint
    throw error
  }

  // Handle empty responses (e.g. 204 No Content)
  const contentType = response.headers.get('content-type')
  if (!contentType || !contentType.includes('application/json')) {
    return null
  }

  return response.json()
}

// ── HTTP method helpers
export const api = {
  get:    (endpoint, options = {}) =>
    request(endpoint, { method: 'GET', ...options }),

  post:   (endpoint, data, options = {}) =>
    request(endpoint, { method: 'POST', body: JSON.stringify(data), ...options }),

  put:    (endpoint, data, options = {}) =>
    request(endpoint, { method: 'PUT', body: JSON.stringify(data), ...options }),

  delete: (endpoint, options = {}) =>
    request(endpoint, { method: 'DELETE', ...options }),
}

export default api
