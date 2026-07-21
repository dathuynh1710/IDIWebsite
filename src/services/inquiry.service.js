/**
 * inquiry.service.js
 * Handles trade inquiry form submissions.
 * Phase 1: Logs to console + simulates success
 * Phase 2: POST to VITE_INQUIRY_ENDPOINT
 */
const INQUIRY_ENDPOINT = import.meta.env.VITE_INQUIRY_ENDPOINT

export const inquiryService = {
  /**
   * Submit a trade inquiry.
   * @param {object} formData
   * @returns {Promise<{ success: boolean, referenceId: string }>}
   */
  submitTrade: async (formData) => {
    if (!INQUIRY_ENDPOINT) {
      // Phase 1: Log and simulate success
      console.info('[InquiryService] Trade inquiry (dev mode):', formData)
      await new Promise(resolve => setTimeout(resolve, 800)) // Simulate network
      return { success: true, referenceId: IDI- }
    }

    const response = await fetch(INQUIRY_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: 'trade', ...formData }),
    })

    if (!response.ok) throw new Error('Failed to submit inquiry')
    return response.json()
  },
}
