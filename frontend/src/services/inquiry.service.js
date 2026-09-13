/**
 * inquiry.service.js
 * Handles contact form submissions through the Laravel API.
 */
import api from './api'

const INQUIRY_ENDPOINT = import.meta.env.VITE_INQUIRY_ENDPOINT
const INQUIRY_TYPE_VALUES = Object.freeze({
  export_quote: 'Báo giá xuất khẩu',
  product_advice: 'Tư vấn sản phẩm',
  business_cooperation: 'Hợp tác kinh doanh',
  investor_relations: 'Quan hệ nhà đầu tư',
  careers: 'Tuyển dụng',
  other: 'Yêu cầu khác',
})

export const inquiryService = {
  /**
   * Submit a trade inquiry.
   * @param {object} formData
   * @returns {Promise<{ success: boolean, referenceId: string }>}
   */
  submitTrade: async (formData, locale = document.documentElement.lang || 'vi') => {
    const response = await api.post(INQUIRY_ENDPOINT || '/contacts', {
      ...formData,
      inquiryType: INQUIRY_TYPE_VALUES[formData.inquiryType] || formData.inquiryType,
      locale,
    })
    return response.data
  },
}
