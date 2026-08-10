import type { ApiResponse, GamificationProfile, GamificationTaskResult, LogroCatalogEntry } from '~/types/api'

export function useGamificationService() {
  const { request } = useApi()

  function getProfile() {
    return request<ApiResponse<GamificationProfile>>('/gamificacion')
  }

  function registerTaskCompletion(tareaId: number) {
    return request<ApiResponse<GamificationTaskResult>>('/gamificacion/tareas-completadas', {
      method: 'POST',
      body: { tarea_id: tareaId }
    })
  }

  function updateSound(sonidos_activados: boolean, volumen: number) {
    return request<ApiResponse<null>>('/gamificacion/sonidos', {
      method: 'PUT',
      body: { sonidos_activados, volumen }
    })
  }

  function getAchievements() {
    return request<ApiResponse<LogroCatalogEntry[]>>('/logros')
  }

  function reset() {
    return request<ApiResponse<null>>('/gamificacion', { method: 'DELETE' })
  }

  return { getProfile, registerTaskCompletion, updateSound, getAchievements, reset }
}
