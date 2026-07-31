import api from './api'

export const careersService = {
  getOpenings: ({ department, locale = 'vi' } = {}) => api
    .get('/careers', { params: { department, locale } })
    .then(response => response.data),

  getById: (slug, locale = 'vi') => api
    .get(`/careers/${slug}`, { params: { locale } })
    .then(response => response.data.data),

  submitApplication: async (application) => {
    const payload = new FormData()
    Object.entries(application).forEach(([key, value]) => {
      if (value !== null && value !== '') payload.append(key, value)
    })

    const response = await api.post('/careers/applications', payload)
    return response.data
  },
}
