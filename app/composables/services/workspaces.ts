import type { ApiResponse, Workspace } from '~/types/api'

export function useWorkspacesService() {
  const { request } = useApi()
  const offline = useOfflineRequest()

  function getAll() {
    return request<ApiResponse<Workspace[]>>('/espacios-trabajo')
  }

  function getById(id: number) {
    return request<ApiResponse<Workspace>>(`/espacios-trabajo/${id}`)
  }

  function create(data: {
    equipo_id: number
    nombre: string
    descripcion?: string
    color?: string
    icono?: string
    orden?: number
  }) {
    return offline.send<Workspace>({
      method: 'POST', url: '/espacios-trabajo', body: { ...data }, resource: 'workspace', type: 'create'
    })
  }

  function update(id: number, data: {
    nombre?: string
    descripcion?: string
    color?: string
    icono?: string
    orden?: number
  }) {
    return offline.send<Workspace>({
      method: 'PUT', url: `/espacios-trabajo/${id}`, body: { ...data, id }, resource: 'workspace', type: 'update'
    })
  }

  function remove(id: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/espacios-trabajo/${id}`, body: { id }, resource: 'workspace', type: 'delete'
    })
  }

  return { getAll, getById, create, update, remove }
}
