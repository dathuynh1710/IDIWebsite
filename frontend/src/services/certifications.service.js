/**
 * certifications.service.js
 */
import { CERTIFICATIONS_DATA } from '@data/certifications'

export const certificationsService = {
  getAll: () => Promise.resolve(CERTIFICATIONS_DATA),

  getById: (id) => {
    const cert = CERTIFICATIONS_DATA.find(c => c.id === id)
    if (!cert) return Promise.reject(new Error(Certification not found: ))
    return Promise.resolve(cert)
  },
}
