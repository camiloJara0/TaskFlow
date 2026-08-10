import type { ApiResponse, User } from '~/types/api'

export function useAuthService() {
  const { request, authToken, user, showSuccess } = useApi()

  async function login(email: string, password: string) {
    const res = await request<ApiResponse<{ user: User }>>('/login', {
      method: 'POST',
      body: { email, password }
    })
    if (res.access_token) {
      authToken.value = res.access_token
      user.value = res.data.user
      localStorage.setItem('user', JSON.stringify(res.data.user))
    }
    showSuccess('Sesión iniciada correctamente')
    return res
  }

  async function register(data: {
    nombre: string
    email: string
    password: string
    password_confirmation: string
  }) {
    const res = await request<ApiResponse<{ user: User }>>('/register', {
      method: 'POST',
      body: data
    })
    showSuccess('Registro exitoso')
    return res
  }

  async function logout() {
    await request<ApiResponse<null>>('/logout', { method: 'POST' })
    authToken.value = null
    user.value = null
    showSuccess('Sesión cerrada')
    navigateTo('/auth/login')
  }

  async function sendVerificationCode(email: string) {
    const res = await request<ApiResponse<null>>('/enviar-codigo', {
      method: 'POST',
      body: { email }
    })
    showSuccess('Código enviado al correo')
    return res
  }

  async function resetPassword(data: {
    email: string
    codigo: string
    password: string
    password_confirmation: string
  }) {
    const res = await request<ApiResponse<null>>('/verificar-codigo-cambio', {
      method: 'POST',
      body: data
    })
    showSuccess('Contraseña actualizada')
    return res
  }

  function getUser() {
    return user
  }

  return { login, register, logout, sendVerificationCode, resetPassword, getUser }
}
