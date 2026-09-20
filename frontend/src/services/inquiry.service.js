/**
 * inquiry.service.js
 * Handles contact form submissions through the Laravel API.
 */
import api from './api'

const INQUIRY_ENDPOINT = import.meta.env.VITE_INQUIRY_ENDPOINT
export const inquiryService = {
  getPage: (locale = document.documentElement.lang || 'vi') => api
    .get(INQUIRY_ENDPOINT || '/contacts', { params: { locale } })
    .then(response => response.data),

  /**
   * Submit a trade inquiry.
   * @param {object} formData
   * @returns {Promise<{ success: boolean, referenceId: string }>}
   */
  submitTrade: async (formData, locale = document.documentElement.lang || 'vi') => {
    const response = await api.post(INQUIRY_ENDPOINT || '/contacts', {
      ...formData,
      locale,
    })
    return response.data
  },
}
