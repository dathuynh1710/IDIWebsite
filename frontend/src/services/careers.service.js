const CAREERS_ENDPOINT = import.meta.env.VITE_CAREERS_ENDPOINT

export const careersService = {
  getOpenings: ({ department } = {}) => Promise.resolve(
    department ? [] : [],
  ),

  getById: (id) => Promise.reject(new Error(`Job not found: ${id}`)),

  submitApplication: async (application) => {
    if (!CAREERS_ENDPOINT) {
      throw new Error('Careers submission endpoint is not configured')
    }

    const payload = new FormData()
    Object.entries(application).forEach(([key, value]) => {
      payload.append(key, value)
    })

    const response = await fetch(CAREERS_ENDPOINT, {
      method: 'POST',
      body: payload,
    })

    if (!response.ok) throw new Error('Failed to submit application')
    return response.json()
  },
}
