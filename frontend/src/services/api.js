import axios from 'axios'
import toast from '@/utils/toast'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
  timeout: 30000,
})

api.interceptors.response.use(
  response => {
    const notification = response.data?.toast
    if (notification?.message) toast.show(notification.message, notification.type)
    return response
  },
  error => {
    const data = error.response?.data
    const notification = data?.toast

    if (notification?.message) {
      toast.show(notification.message, notification.type || 'error')
    } else if (data?.errors) {
      toast.validation(data.errors)
    } else {
      toast.error(data?.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại.')
    }

    return Promise.reject(error)
  },
)

export default api
export { api }
