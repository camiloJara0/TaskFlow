import type { User } from '~/types/api'
import type { ResponseType } from 'ofetch'

// const BASE_URL = 'http://localhost:8000/api/v1'
const BASE_URL = 'https://taskflow.zeus01one/api/v1'

export function useApi() {
  const toast = useToast()
  const authToken = useCookie('auth_token')
  const user = useState<User | null>('auth_user', () => null)

  async function request<T = any>(
    url: string,
    options: { method?: string; body?: any; headers?: Record<string, string>; responseType?: ResponseType } = {}
  ): Promise<T> {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options.headers || {})
    }

    if (authToken.value) {
      headers.Authorization = `Bearer ${authToken.value}`
    }

    const isFormData = options.body instanceof FormData
    if (!isFormData) {
      headers['Content-Type'] = 'application/json'
    }

    try {
      const response = await $fetch<T>(`${BASE_URL}${url}`, {
        ...options,
        method: (options.method || 'GET') as any,
        headers,
        onResponseError({ response: res }) {
          handleError(res.status, res._data)
        }
      })

      return response
    } catch (error: any) {
      if (error?.statusCode === 401) {
        authToken.value = null
        user.value = null
        navigateTo('/auth/login')
      }
      throw error
    }
  }

  function handleError(status: number, data: any) {
    const messages: Record<number, string> = {
      400: 'Datos inválidos. Verifica los campos.',
      401: 'Sesión expirada. Inicia sesión nuevamente.',
      403: 'No tienes permiso para realizar esta acción.',
      404: 'Recurso no encontrado.',
      422: data?.message || 'Error de validación.',
      429: 'Demasiadas solicitudes. Intenta más tarde.',
      500: 'Error del servidor. Intenta nuevamente.'
    }

    const message = data?.message || messages[status] || 'Error inesperado.'

    if (status === 422 && data?.errors) {
      const fields = Object.values(data.errors).flat()
      toast.add({
        title: 'Error de validación',
        description: (fields as string[]).join('. '),
        color: 'error',
        icon: 'i-lucide-alert-circle'
      })
    } else {
      toast.add({
        title: `Error ${status}`,
        description: message,
        color: 'error',
        icon: 'i-lucide-alert-circle'
      })
    }
  }

  function showSuccess(message: string) {
    toast.add({
      title: 'Operación exitosa',
      description: message,
      color: 'success',
      icon: 'i-lucide-check-circle'
    })
  }

  return { request, showSuccess, authToken, user }
}
