import type { ApiResponse, Team, TeamMember } from '~/types/api'

export function useTeamsService() {
  const { request } = useApi()
  const offline = useOfflineRequest()

  function getAll() {
    return request<ApiResponse<Team[]>>('/equipos')
  }

  function getById(id: number) {
    return request<ApiResponse<Team>>(`/equipos/${id}`)
  }

  function create(data: { nombre: string; descripcion?: string; icono?: string; color?: string }) {
    return offline.send<Team>({
      method: 'POST', url: '/equipos', body: { ...data }, resource: 'team', type: 'create'
    })
  }

  function update(id: number, data: {
    nombre?: string
    descripcion?: string
    icono?: string
    color?: string
    estado?: string
  }) {
    return offline.send<Team>({
      method: 'PUT', url: `/equipos/${id}`, body: { ...data, id }, resource: 'team', type: 'update'
    })
  }

  function remove(id: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/equipos/${id}`, body: { id }, resource: 'team', type: 'delete'
    })
  }

  function getMembers(teamId: number) {
    return request<ApiResponse<TeamMember[]>>(`/equipos/${teamId}/miembros`)
  }

  function addMember(teamId: number, data: { usuario_id: number; rol?: string }) {
    return offline.send<TeamMember>({
      method: 'POST', url: `/equipos/${teamId}/miembros`, body: { ...data, equipo_id: teamId }, resource: 'team', type: 'create'
    })
  }

  function removeMember(teamId: number, userId: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/equipos/${teamId}/miembros/${userId}`, body: { id: userId }, resource: 'team', type: 'delete'
    })
  }

  function getMembersWithRoles(teamId: number) {
    return request<ApiResponse<any[]>>(`/equipos/${teamId}/miembros-rol`)
  }

  function updateMemberRole(memberId: number, data: { rol: string; permisos?: string[] }) {
    return request<ApiResponse<TeamMember>>(`/miembros-equipo/${memberId}`, {
      method: 'PUT', body: data
    })
  }

  function removeMemberById(memberId: number) {
    return request<ApiResponse<null>>(`/miembros-equipo/${memberId}`, { method: 'DELETE' })
  }

  async function syncMembers(teamId: number, userIds: number[]) {
    const current = await getMembers(teamId)
    const currentIds = current.data.map((m: any) => m.usuario?.id ?? m.usuario_id)
    
    const toAdd = userIds.filter((id) => !currentIds.includes(id))
    const toRemove = currentIds.filter((id) => !userIds.includes(id))

    for (const usuarioId of toAdd) {
      await addMember(teamId, { usuario_id: usuarioId, rol: 'Miembro' })
    }
    for (const usuarioId of toRemove) {
      await removeMember(teamId, usuarioId)
    }
  }

  return {
    getAll, getById, create, update, remove,
    getMembers, addMember, removeMember,
    getMembersWithRoles, updateMemberRole, removeMemberById, syncMembers
  }
}
