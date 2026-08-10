export type ReunionEstadoKey = 'pendiente' | 'confirmada' | 'en_curso' | 'finalizada' | 'cancelada'

export function reunionEstadoKey(value: unknown): ReunionEstadoKey {
  const v = String(value ?? '').trim().toLowerCase()
  if (v === 'confirmada' || v === 'confirmado') return 'confirmada'
  if (v === 'en_curso' || v === 'en curso' || v === 'encurso') return 'en_curso'
  if (v === 'finalizada' || v === 'finalizado') return 'finalizada'
  if (v === 'cancelada' || v === 'cancelado') return 'cancelada'
  return 'pendiente'
}

export function reunionEstadoLabel(value: unknown): string {
  const map: Record<ReunionEstadoKey, string> = {
    pendiente: 'Pendiente',
    confirmada: 'Confirmada',
    en_curso: 'En curso',
    finalizada: 'Finalizada',
    cancelada: 'Cancelada'
  }
  return map[reunionEstadoKey(value)]
}

export function reunionEstadoBadgeColor(value: unknown): 'neutral' | 'info' | 'success' | 'error' | 'warning' {
  const map: Record<ReunionEstadoKey, 'neutral' | 'info' | 'success' | 'error' | 'warning'> = {
    pendiente: 'neutral',
    confirmada: 'info',
    en_curso: 'success',
    finalizada: 'neutral',
    cancelada: 'error'
  }
  return map[reunionEstadoKey(value)]
}

export function reunionEstadoChipClass(value: unknown): string {
  const map: Record<ReunionEstadoKey, string> = {
    pendiente: 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400',
    confirmada: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    en_curso: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    finalizada: 'bg-white/70 text-slate-500 dark:bg-white/10 dark:text-slate-400',
    cancelada: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
  }
  return map[reunionEstadoKey(value)]
}

export function parseReunionDate(value: unknown): Date | null {
  if (value === null || value === undefined || value === '') return null
  const d = new Date(String(value))
  return Number.isNaN(d.getTime()) ? null : d
}

export function formatReunionTime(value: unknown): string {
  if (value === null || value === undefined || value === '') return ''
  const v = String(value).trim()
  if (/^\d{2}:\d{2}(:\d{2})?$/.test(v)) return v.slice(0, 5)
  const d = new Date(`1970-01-01T${v}`)
  if (Number.isNaN(d.getTime())) return ''
  return d.toTimeString().slice(0, 5)
}

export function reunionDayLabel(value: unknown): string {
  const d = parseReunionDate(value)
  if (!d) return '—'
  return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })
}

interface ReunionIntegrantesLike {
  integrantes?: { usuario?: { nombre?: string } }[]
}

export function reunionIntegrantesNames(reunion: ReunionIntegrantesLike | null | undefined): string {
  if (!reunion?.integrantes) return ''
  const names = reunion.integrantes
    .map(i => i.usuario?.nombre)
    .filter(Boolean)
  return names.join(', ')
}

interface ReunionJoinLike {
  url?: string | null
  estado?: unknown
}

export function reunionCanJoin(reunion: ReunionJoinLike | null | undefined): boolean {
  if (!reunion?.url) return false
  const estado = reunionEstadoKey(reunion.estado)
  return estado !== 'finalizada' && estado !== 'cancelada'
}
