export type PriorityKey = 'urgent' | 'high' | 'medium' | 'low'

export type StatusKey = 'backlog' | 'todo' | 'in_progress' | 'review' | 'done'

export function priorityKey(value: unknown): PriorityKey {
  const v = String(value ?? '').trim().toLowerCase()
  if (v === 'urgente' || v === 'urgent' || v === 'critica' || v === 'critical' || v === 'maxima') return 'urgent'
  if (v === 'alta' || v === 'high' || v === 'alta_prioridad') return 'high'
  if (v === 'baja' || v === 'low' || v === 'baja_prioridad') return 'low'
  return 'medium'
}

export function priorityLabel(value: unknown): string {
  const map: Record<PriorityKey, string> = { urgent: 'Urgente', high: 'Alta', medium: 'Media', low: 'Baja' }
  return map[priorityKey(value)]
}

export function priorityBadgeClass(value: unknown): string {
  const map: Record<PriorityKey, string> = {
    urgent: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
    high: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'
  }
  return map[priorityKey(value)]
}

export function prioritySolidClass(value: unknown): string {
  const map: Record<PriorityKey, string> = {
    urgent: 'bg-gradient-to-r from-red-500 to-rose-500 shadow-red-500/30',
    high: 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-amber-500/30',
    medium: 'bg-gradient-to-r from-blue-500 to-blue-600 shadow-blue-500/30',
    low: 'bg-gradient-to-r from-slate-400 to-slate-500 shadow-slate-400/30'
  }
  return map[priorityKey(value)]
}

export function priorityDotClass(value: unknown): string {
  const map: Record<PriorityKey, string> = {
    urgent: 'bg-red-500',
    high: 'bg-amber-500',
    medium: 'bg-blue-500',
    low: 'bg-gray-400'
  }
  return map[priorityKey(value)]
}

export function statusKey(value: unknown): StatusKey {
  const v = String(value ?? '').trim().toLowerCase()
  if (v === 'backlog' || v === 'pendiente' || v === 'sin_estado' || v === '') return 'backlog'
  if (v === 'todo' || v === 'por_hacer' || v === 'por hacer' || v === 'porhacer') return 'todo'
  if (v === 'in_progress' || v === 'en_progreso' || v === 'en progreso' || v === 'progreso' || v === 'inprogress') return 'in_progress'
  if (v === 'review' || v === 'revision' || v === 'revisión' || v === 'en_revision') return 'review'
  if (v === 'done' || v === 'completado' || v === 'completada' || v === 'completa' || v === 'hecho') return 'done'
  return 'backlog'
}

export function statusLabel(value: unknown): string {
  const map: Record<StatusKey, string> = {
    backlog: 'Pendiente',
    todo: 'Por hacer',
    in_progress: 'En progreso',
    review: 'Revisión',
    done: 'Completado'
  }
  return map[statusKey(value)]
}

export function statusBadgeColor(value: unknown): 'success' | 'info' | 'warning' | 'neutral' {
  const map: Record<StatusKey, 'success' | 'info' | 'warning' | 'neutral'> = {
    backlog: 'neutral',
    todo: 'info',
    in_progress: 'warning',
    review: 'warning',
    done: 'success'
  }
  return map[statusKey(value)]
}

export function statusDotClass(value: unknown): string {
  const map: Record<StatusKey, string> = {
    backlog: 'bg-gray-400',
    todo: 'bg-blue-500',
    in_progress: 'bg-amber-500',
    review: 'bg-violet-500',
    done: 'bg-green-500'
  }
  return map[statusKey(value)]
}

export function statusRingClass(value: unknown): string {
  const map: Record<StatusKey, string> = {
    backlog: 'ring-gray-400',
    todo: 'ring-blue-500',
    in_progress: 'ring-amber-500',
    review: 'ring-violet-500',
    done: 'ring-green-500'
  }
  return map[statusKey(value)]
}

export function taskAssigneeName(task: Record<string, any> | null | undefined): string {
  if (!task) return '—'
  return task.responsable?.nombre || task.creador?.nombre || (task.responsable_id ? String(task.responsable_id) : '—')
}

export function taskAssigneeInitials(task: Record<string, any> | null | undefined): string {
  const name = taskAssigneeName(task)
  if (!name || name === '—') return '?'
  const parts = name.split(' ').filter(Boolean)
  if (parts.length > 1) return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase()
  return name.slice(0, 2).toUpperCase()
}

export function parseTaskDate(value: unknown): Date | null {
  if (value === null || value === undefined || value === '') return null
  const d = new Date(String(value))
  return Number.isNaN(d.getTime()) ? null : d
}

export function formatShortDate(value: unknown): string {
  const d = parseTaskDate(value)
  if (!d) return '—'
  return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })
}

export function formatLongDate(value: unknown): string {
  const d = parseTaskDate(value)
  if (!d) return '—'
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}

export function taskProgress(task: Record<string, any> | null | undefined): number {
  if (!task) return 0
  const p = Number(task.porcentaje)
  return Number.isFinite(p) ? Math.max(0, Math.min(100, p)) : 0
}

export function taskDurationHours(task: Record<string, any> | null | undefined): number {
  if (!task) return 0
  const h = Number(task.estimacion_horas)
  return Number.isFinite(h) ? h : 0
}
