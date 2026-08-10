import type { ApiResponse, Reunion } from '~/types/api'

export function useReunionsService() {
  const { request } = useApi()
  const offline = useOfflineRequest()

  function getAll(espacioTrabajoId?: number) {
    const query = espacioTrabajoId ? `?espacio_trabajo_id=${espacioTrabajoId}` : ''
    return request<ApiResponse<Reunion[]>>(`/reuniones${query}`)
  }

  function getById(id: number) {
    return request<ApiResponse<Reunion>>(`/reuniones/${id}`)
  }

  function create(data: {
    espacio_trabajo_id?: number | null
    titulo: string
    descripcion?: string | null
    estado?: string
    fecha: string
    hora: string
    url?: string | null
    archivada?: boolean
    integrantes?: number[]
  }) {
    return offline.send<Reunion>({
      method: 'POST', url: '/reuniones', body: { ...data }, resource: 'reunion', type: 'create'
    })
  }

  function update(id: number, data: {
    espacio_trabajo_id?: number | null
    titulo?: string
    descripcion?: string | null
    estado?: string
    fecha?: string
    hora?: string
    url?: string | null
    archivada?: boolean
    integrantes?: number[]
  }) {
    return offline.send<Reunion>({
      method: 'PUT', url: `/reuniones/${id}`, body: { ...data, id }, resource: 'reunion', type: 'update'
    })
  }

  function remove(id: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/reuniones/${id}`, body: { id }, resource: 'reunion', type: 'delete'
    })
  }

  function changeStatus(id: number, estado: string) {
    return offline.send<Reunion>({
      method: 'PUT', url: `/reuniones/${id}/cambiar-estado`, body: { id, estado }, resource: 'reunion', type: 'update'
    })
  }

  function toggleArchive(id: number) {
    return offline.send<Reunion>({
      method: 'PUT', url: `/reuniones/${id}/archivar`, body: { id }, resource: 'reunion', type: 'update'
    })
  }

  return { getAll, getById, create, update, remove, changeStatus, toggleArchive }
}
