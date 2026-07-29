import api from './api'

export async function getApiHealth() {
  const response = await api.get('/health')

  return response.data
}
