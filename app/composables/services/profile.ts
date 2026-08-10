import type { ApiResponse, User } from '~/types/api'

export function useProfileService() {
  const { request, showSuccess } = useApi()

  async function getProfile() {
    return await request<ApiResponse<User>>('/perfil')
  }

  async function getProfiles() {
    return await request<ApiResponse<User>>('/perfiles')
  }

  async function updateProfile(data: {
    nombre?: string
    foto?: string | null
    zona_horaria?: string
    idioma?: string
    tema?: string
  }, foto: File) {
    const formData = new FormData()
    formData.append('archivo', foto)
    if (data.nombre) formData.append('nombre', data.nombre)
    if (data.zona_horaria) formData.append('zona_horaria', data.zona_horaria)
    if (data.idioma) formData.append('idioma', data.idioma)
    if (data.tema) formData.append('tema', data.tema)
    formData.append("_method", "PUT");

    const res = await request<ApiResponse<User>>('/perfil', {
      method: 'POST',
      body: formData
    })
    showSuccess('Perfil actualizado')
    return res
  }

  async function getAppearance() {
    return request<ApiResponse<AppearanceSettings>>('/perfil/apariencia')
  }

  async function updateAppearance(data: Partial<AppearanceSettings>) {
    return request<ApiResponse<AppearanceSettings>>('/perfil/apariencia', {
      method: 'PUT',
      body: data
    })
  }

  return { getProfile, getProfiles, updateProfile, getAppearance, updateAppearance }
}
