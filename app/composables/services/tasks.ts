import type { ApiResponse, Task, SubTask, Comment } from '~/types/api'

export function useTasksService() {
  const { request } = useApi()
  const offline = useOfflineRequest()

  function getAll() {
    return request<ApiResponse<Task[]>>('/tareas')
  }

  function getById(id: number) {
    return request<ApiResponse<Task>>(`/tareas/${id}`)
  }

  function create(data: {
    lista_id: number
    titulo: string
    estado: string
    responsable_id?: number
    descripcion?: string
    prioridad?: string
    fecha_inicio?: string
    fecha_vencimiento?: string
    estimacion_horas?: number
    orden?: number
    es_recurrente?: boolean
    etiquetas?: number[]
  }) {
    return offline.send<Task>({
      method: 'POST', url: '/tareas', body: { ...data }, resource: 'task', type: 'create'
    })
  }

  function update(id: number, data: {
    titulo?: string
    lista_id?: number
    estado?: string
    responsable_id?: number | null
    descripcion?: string
    prioridad?: string
    fecha_inicio?: string
    fecha_vencimiento?: string
    estimacion_horas?: number
    horas_invertidas?: number
    porcentaje?: number
    orden?: number
    es_recurrente?: boolean
    archivada?: boolean
    etiquetas?: number[]
  }) {
    return offline.send<Task>({
      method: 'PUT', url: `/tareas/${id}`, body: { ...data, id }, resource: 'task', type: 'update'
    })
  }

  function remove(id: number) {
    return offline.send<null>({
      method: 'DELETE', url: `/tareas/${id}`, body: { id }, resource: 'task', type: 'delete'
    })
  }

  function changeStatus(id: number, estado_id: number) {
    return offline.send<Task>({
      method: 'PUT', url: `/tareas/${id}/cambiar-estado`, body: { id, estado_id }, resource: 'task', type: 'update'
    })
  }

  function assignResponsible(id: number, responsable_id: number | null) {
    return offline.send<Task>({
      method: 'PUT', url: `/tareas/${id}/asignar-responsable`, body: { id, responsable_id }, resource: 'task', type: 'update'
    })
  }

  function reorder(orden: { id: number, orden: number, lista_id?: number }[]) {
    return request<ApiResponse<null>>('/tareas/reordenar', {
      method: 'POST', body: { orden }
    })
  }

  function getSubtasks(taskId: number) {
    return request<ApiResponse<SubTask[]>>(`/tareas/${taskId}/subtareas`)
  }

  function addSubtask(taskId: number, data: { titulo: string, orden?: number }) {
    return request<ApiResponse<SubTask>>(`/tareas/${taskId}/subtareas`, {
      method: 'POST', body: data
    })
  }

  function updateSubtask(subtaskId: number, data: { titulo?: string, orden?: number }) {
    return request<ApiResponse<SubTask>>(`/subtareas/${subtaskId}`, {
      method: 'PUT', body: data
    })
  }

  function removeSubtask(subtaskId: number) {
    return request<ApiResponse<null>>(`/subtareas/${subtaskId}`, { method: 'DELETE' })
  }

  function toggleSubtask(subtaskId: number, estado: boolean) {
    return request<ApiResponse<SubTask>>(`/subtareas/${subtaskId}/cambiar-estado`, {
      method: 'PUT', body: { estado }
    })
  }

  function getComments(taskId: number) {
    return request<ApiResponse<Comment[]>>(`/tareas/${taskId}/comentarios`)
  }

  function addComment(taskId: number, comentario: string) {
    return request<ApiResponse<Comment>>(`/tareas/${taskId}/comentarios`, {
      method: 'POST', body: { comentario }
    })
  }

  function updateComment(commentId: number, comentario: string) {
    return request<ApiResponse<Comment>>(`/comentarios/${commentId}`, {
      method: 'PUT', body: { comentario }
    })
  }

  function removeComment(commentId: number) {
    return request<ApiResponse<null>>(`/comentarios/${commentId}`, { method: 'DELETE' })
  }

  function addLabel(taskId: number, comentario: string) {
    return request<ApiResponse<Comment>>(`/tareas/${taskId}/comentarios`, {
      method: 'POST', body: { comentario }
    })
  }

  function addReaction(comentario_id: number, tipo: string) {
    return request<ApiResponse<any>>('/reacciones', {
      method: 'POST', body: { comentario_id, tipo }
    })
  }

  function removeReaction(reaccionId: number) {
    return request<ApiResponse<null>>(`/reacciones/${reaccionId}`, { method: 'DELETE' })
  }

  function getDependencies(taskId: number) {
    return request<ApiResponse<any[]>>(`/tareas/${taskId}/dependencias`)
  }

  function addDependency(taskId: number, data: { tarea_hija_id: number, tipo?: string }) {
    return request<ApiResponse<any>>(`/tareas/${taskId}/dependencias`, {
      method: 'POST', body: data
    })
  }

  function removeDependency(dependencyId: number) {
    return request<ApiResponse<null>>(`/dependencias/${dependencyId}`, { method: 'DELETE' })
  }

  function getFiles(taskId: number) {
    return request<ApiResponse<any[]>>(`/tareas/${taskId}/archivos`)
  }

  function uploadFile(taskId: number, file: File, nombre?: string) {
    const formData = new FormData()
    formData.append('archivo', file)
    if (nombre) formData.append('nombre', nombre)
    return request<ApiResponse<any>>(`/tareas/${taskId}/archivos`, {
      method: 'POST', body: formData
    })
  }

  function downloadFile(fileId: number) {
    return request<Blob>(`/archivos/${fileId}/descargar`, { responseType: 'blob' })
  }

  function removeFile(fileId: number) {
    return request<ApiResponse<null>>(`/archivos/${fileId}`, { method: 'DELETE' })
  }

  function getChecklists(taskId: number) {
    return request<ApiResponse<any[]>>(`/tareas/${taskId}/checklists`)
  }

  function addChecklist(taskId: number, data: { titulo: string, orden?: number }) {
    return request<ApiResponse<any>>(`/tareas/${taskId}/checklists`, {
      method: 'POST', body: data
    })
  }

  function updateChecklist(checklistId: number, data: { titulo?: string, orden?: number }) {
    return request<ApiResponse<any>>(`/checklists/${checklistId}`, {
      method: 'PUT', body: data
    })
  }

  function removeChecklist(checklistId: number) {
    return request<ApiResponse<null>>(`/checklists/${checklistId}`, { method: 'DELETE' })
  }

  function toggleChecklist(checklistId: number, estado: boolean) {
    return request<ApiResponse<any>>(`/checklists/${checklistId}/cambiar-estado`, {
      method: 'PUT', body: { estado }
    })
  }

  function reorderChecklists(orden: { id: number, orden: number }[]) {
    return request<ApiResponse<null>>('/checklists/reordenar', {
      method: 'POST', body: { orden }
    })
  }

  function getActivity(taskId: number) {
    return request<ApiResponse<any[]>>(`/tareas/${taskId}/actividades`)
  }

  function addReminder(taskId: number, data: { fecha: string, tipo?: string }) {
    return request<ApiResponse<any>>('/recordatorios', {
      method: 'POST', body: { tarea_id: taskId, ...data }
    })
  }

  function removeReminder(reminderId: number) {
    return request<ApiResponse<null>>(`/recordatorios/${reminderId}`, { method: 'DELETE' })
  }

  function getByWorkspace(workspaceId: number) {
    return request<ApiResponse<any[]>>(`/workspaces/${workspaceId}/tareas`)
  }

  return {
    getAll, getById, create, update, remove,
    changeStatus, assignResponsible, reorder,
    getSubtasks, addSubtask, updateSubtask, removeSubtask, toggleSubtask,
    getComments, addComment, updateComment, removeComment,
    addReaction, removeReaction,
    getDependencies, addDependency, removeDependency,
    getFiles, uploadFile, downloadFile, removeFile,
    getChecklists, addChecklist, updateChecklist, removeChecklist, toggleChecklist, reorderChecklists,
    getActivity, addReminder, removeReminder,
    getByWorkspace
  }
}
