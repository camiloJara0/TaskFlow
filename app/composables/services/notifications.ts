import type { ApiResponse, Notification, Activity } from '~/types/api'

export function useNotificationsService() {
  const { request } = useApi()
  const offline = useOfflineRequest()

  function getAll() {
    return request<ApiResponse<Notification[]>>('/notificaciones')
  }

  function markAsRead(id: number) {
    return offline.send<Notification>({
      method: 'PUT', url: `/notificaciones/${id}/leer`, body: { id }, resource: 'notification', type: 'update'
    })
  }

  function markAllAsRead() {
    return offline.send<null>({
      method: 'PUT', url: '/notificaciones/leer-todas', body: {}, resource: 'notification', type: 'update'
    })
  }

  function remove(id: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/notificaciones/${id}`, body: { id }, resource: 'notification', type: 'delete'
    })
  }

  function getReminders() {
    return request<ApiResponse<any[]>>('/recordatorios')
  }

  function getGlobalActivity() {
    return request<ApiResponse<Activity[]>>('/actividades')
  }

  function getTaskActivity(taskId: number) {
    return request<ApiResponse<Activity[]>>(`/tareas/${taskId}/actividades`)
  }

  function getActivityDetail(id: number) {
    return request<ApiResponse<Activity>>(`/actividades/${id}`)
  }

  function registerPushToken(token: string, navegador?: string) {
    return request<ApiResponse<null>>('/push-tokens', {
      method: 'POST', body: { token, navegador }
    })
  }

  function removePushToken(token: string) {
    return request<ApiResponse<null>>(`/push-tokens/${token}`, { method: 'DELETE' })
  }

  function getFavorites() {
    return request<ApiResponse<any[]>>('/favoritos')
  }

  function addFavorite(favoritable_type: string, favoritable_id: number) {
    return request<ApiResponse<any>>('/favoritos', {
      method: 'POST', body: { favoritable_type, favoritable_id }
    })
  }

  function removeFavorite(id: number) {
    return request<ApiResponse<null>>(`/favoritos/${id}`, { method: 'DELETE' })
  }

  function getAuditLog(page = 1) {
    return request<ApiResponse<{ current_page: number; data: any[]; last_page: number; total: number }>>(
      `/auditoria?page=${page}`
    )
  }

  function getAuditDetail(id: number) {
    return request<ApiResponse<any>>(`/auditoria/${id}`)
  }

  return {
    getAll, markAsRead, markAllAsRead, remove,
    getReminders,
    getGlobalActivity, getTaskActivity, getActivityDetail,
    registerPushToken, removePushToken,
    getFavorites, addFavorite, removeFavorite,
    getAuditLog, getAuditDetail
  }
}
