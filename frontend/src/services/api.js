import axios from 'axios'
import toast from '@/utils/toast'
import { getStoredLanguage } from '@context/LanguageContext'
import { TRANSLATIONS } from '@/i18n/translations'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
  timeout: 30000,
})

api.interceptors.request.use((config) => {
  const locale = getStoredLanguage()
  config.params = { ...config.params, locale }
  config.headers['Accept-Language'] = locale
  return config
})

api.interceptors.response.use(
  response => {
    const notification = response.data?.toast
    if (notification?.message) toast.show(notification.message, notification.type)
    return response
  },
  error => {
    if (axios.isCancel(error) || error?.code === 'ERR_CANCELED') {
      return Promise.reject(error)
    }

    const data = error.response?.data
    const notification = data?.toast

    if (notification?.message) {
      toast.show(notification.message, notification.type || 'error')
    } else if (data?.errors) {
      toast.validation(data.errors)
    } else {
      const locale = getStoredLanguage()
      toast.error(data?.message || TRANSLATIONS[locale].api.connectionError)
    }

    return Promise.reject(error)
  },
)

export default api
export { api }
