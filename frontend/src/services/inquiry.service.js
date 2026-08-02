/**
 * inquiry.service.js
 * Handles contact form submissions through the Laravel API.
 */
import api from './api'

const INQUIRY_ENDPOINT = import.meta.env.VITE_INQUIRY_ENDPOINT

export const inquiryService = {
  /**
   * Submit a trade inquiry.
   * @param {object} formData
   * @returns {Promise<{ success: boolean, referenceId: string }>}
   */
  submitTrade: async (formData) => {
    const response = await api.post(INQUIRY_ENDPOINT || '/contacts', {
      ...formData,
      locale: document.documentElement.lang?.split('-')[0] || 'vi',
    })
    return response.data
  },
}
