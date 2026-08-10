export type NotificationKind = 'comentario' | 'asignacion' | 'recordatorio' | 'cambio_estado' | 'mencion' | 'reunion' | 'sistema'

export function notificationKind(value: unknown): NotificationKind {
  const v = String(value ?? '').trim().toLowerCase()
  if (v === 'comentario' || v === 'comment') return 'comentario'
  if (v === 'asignacion' || v === 'assignment') return 'asignacion'
  if (v === 'recordatorio' || v === 'reminder') return 'recordatorio'
  if (v === 'cambio_estado' || v === 'status' || v === 'cambio') return 'cambio_estado'
  if (v === 'mencion' || v === 'mention') return 'mencion'
  if (v === 'reunion' || v === 'reunión' || v === 'meeting' || v === 'invitacion' || v === 'invitación') return 'reunion'
  return 'sistema'
}

export function notificationLabel(value: unknown): string {
  const map: Record<NotificationKind, string> = {
    comentario: 'Comentario',
    asignacion: 'Asignación',
    recordatorio: 'Recordatorio',
    cambio_estado: 'Cambio de estado',
    mencion: 'Mención',
    reunion: 'Reunión',
    sistema: 'Sistema'
  }
  return map[notificationKind(value)]
}

export function notificationStyle(value: unknown): { icon: string, color: string, bg: string } {
  const map: Record<NotificationKind, { icon: string, color: string, bg: string }> = {
    comentario: {
      icon: 'i-lucide-message-square',
      color: 'text-blue-500',
      bg: 'bg-blue-500/10 ring-blue-500/20'
    },
    asignacion: {
      icon: 'i-lucide-user-plus',
      color: 'text-violet-500',
      bg: 'bg-violet-500/10 ring-violet-500/20'
    },
    recordatorio: {
      icon: 'i-lucide-clock',
      color: 'text-amber-500',
      bg: 'bg-amber-500/10 ring-amber-500/20'
    },
    cambio_estado: {
      icon: 'i-lucide-arrow-right-left',
      color: 'text-orange-500',
      bg: 'bg-orange-500/10 ring-orange-500/20'
    },
    mencion: {
      icon: 'i-lucide-at-sign',
      color: 'text-pink-500',
      bg: 'bg-pink-500/10 ring-pink-500/20'
    },
    reunion: {
      icon: 'i-lucide-video',
      color: 'text-violet-500',
      bg: 'bg-violet-500/10 ring-violet-500/20'
    },
    sistema: {
      icon: 'i-lucide-bell',
      color: 'text-slate-500',
      bg: 'bg-slate-500/10 ring-slate-500/20'
    }
  }
  return map[notificationKind(value)]
}

export function notificationUrl(n: { url?: string | null }): string | null {
  const url = (n.url || '').trim()
  if (!url) return null
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url
  return `/${url}`
}

export function notificationTimeAgo(value: unknown): string {
  if (!value) return ''
  const date = new Date(String(value))
  if (Number.isNaN(date.getTime())) return ''

  const diffMs = Date.now() - date.getTime()
  const future = diffMs < 0
  const abs = Math.abs(diffMs)
  const seconds = Math.round(abs / 1000)
  const minutes = Math.round(seconds / 60)
  const hours = Math.round(minutes / 60)
  const days = Math.round(hours / 24)
  const weeks = Math.round(days / 7)

  const suffix = future ? 'en ' : 'hace '

  if (minutes < 1) return future ? 'ahora' : 'ahora mismo'
  if (minutes < 60) return `${suffix}${minutes} min`
  if (hours < 24) return `${suffix}${hours} h`
  if (days < 7) return `${suffix}${days} día${days === 1 ? '' : 's'}`
  if (weeks < 5) return `${suffix}${weeks} sem`
  return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })
}
