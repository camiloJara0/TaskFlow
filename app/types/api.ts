export interface ApiResponse<T = any> {
  success: boolean
  message?: string
  data: T
  access_token?: string
  token_type?: string
  expires_in?: number
}

export interface PaginatedData<T> {
  current_page: number
  data: T[]
  last_page: number
  total: number
}

export interface User {
  id: number
  nombre: string
  email: string
  foto: string | null
  estado: string
  ultimo_login?: string
  zona_horaria: string
  idioma: string
  tema: string
}

export interface Team {
  id: number
  nombre: string
  descripcion: string | null
  icono: string | null
  color: string | null
  propietario_id: number
  estado: string
  propietario: User
  miembros?: TeamMember[]
}

export interface TeamMember {
  id: number
  equipo_id: number
  usuario_id: number
  rol: string
  permisos: string[]
  usuario: User
}

export interface Workspace {
  id: number
  equipo_id: number
  nombre: string
  descripcion: string | null
  color: string | null
  icono: string | null
  orden: number
  equipo?: Team
}

export interface Status {
  id: number
  workspace_id: number
  nombre: string
  color: string | null
  orden: number
}

export interface Board {
  id: number
  workspace_id: number
  nombre: string
  color: string | null
  orden: number
}

export interface Tag {
  id: number
  workspace_id: number
  nombre: string
  color: string | null
  icono: string | null
}

export interface Task {
  id: number
  lista_id: number
  titulo: string
  estado_id: number
  responsable_id: number | null
  descripcion: string | null
  prioridad: string | null
  fecha_inicio: string | null
  fecha_vencimiento: string | null
  estimacion_horas: number | null
  horas_invertidas: number | null
  porcentaje: number | null
  orden: number
  es_recurrente: boolean
  archivada: boolean
  lista?: Board
  creador?: User
  responsable?: User
  estado?: Status
  etiquetas?: Tag[]
  subtareas?: SubTask[]
  archivos?: any[]
  recordatorios?: any[]
}

export interface SubTask {
  id: number
  tarea_id: number
  titulo: string
  estado: boolean
  orden: number
}

export interface Reaction {
  id: number
  usuario_id: number
  comentario_id: number
  tipo: string
  usuario?: User
}

export interface Comment {
  id: number
  tarea_id: number
  usuario_id: number
  comentario: string
  created_at?: string
  usuario: User
  reacciones?: Reaction[]
}

export interface Notification {
  id: number
  usuario_id: number
  titulo: string
  mensaje: string
  tipo: string
  url: string | null
  leida: boolean
  created_at: string
}

export interface Activity {
  id: number
  usuario_id: number
  tipo: string
  objeto_type: string
  objeto_id: number
  descripcion: string
  usuario: User
}

/* ---------- Gamificación ---------- */

export interface GamificationDiaryEntry {
  fecha: string
  tareas: number
  xp: number
}

export interface GamificationLogro {
  slug: string
  desbloqueado_en: string
}

export interface GamificationProfile {
  xp: number
  nivel: number
  racha: number
  mejor_racha: number
  ultimo_dia_activo: string | null
  tareas_completadas: number
  tareas_completadas_ids: number[]
  sonidos_activados: boolean
  volumen: number
  diario: GamificationDiaryEntry[]
  logros: GamificationLogro[]
}

export interface LogroNuevo {
  slug: string
  titulo: string
  descripcion: string
  icono: string
  rareza: string
}

export interface GamificationTaskResult {
  xp_ganado: number
  subio_nivel: boolean
  nuevo_nivel: number | null
  hito_racha: number | null
  logros_nuevos: LogroNuevo[]
  perfil: GamificationProfile
}

export interface LogroCatalogEntry extends LogroNuevo {
  categoria: string
  condicion: string | null
  meta: number | null
  desbloqueado: boolean
  desbloqueado_en: string | null
}
