import type { Notification } from '~/types/api'

let pollingStarted = false

export function useNotifications() {
  const store = useOfflineStore()
  const { getAll, markAsRead, markAllAsRead, remove: removeNotification } = useNotificationsService()

  const notifications = computed(() => (store.collections.notifications ?? []) as unknown as Notification[])
  const loading = computed(() => store.loading.notifications ?? false)

  const unreadCount = computed(() => notifications.value.filter(n => !n.leida).length)

  async function refresh() {
    await store.loadCollection('notifications', () => getAll(), { ttlMs: 60_000 })
  }

  async function markRead(id: number) {
    const target = notifications.value.find(n => n.id === id)
    if (!target || target.leida) return
    const prev = target.leida
    target.leida = true
    try {
      await markAsRead(id)
      void store.persistCollection('notifications')
    } catch {
      target.leida = prev
    }
  }

  async function markAll() {
    if (unreadCount.value === 0) return
    const prev = notifications.value.map(n => n.leida)
    notifications.value.forEach((n) => {
      n.leida = true
    })
    try {
      await markAllAsRead()
      void store.persistCollection('notifications')
    } catch {
      notifications.value.forEach((n, i) => {
        n.leida = prev[i]!
      })
    }
  }

  async function remove(id: number) {
    const prev = notifications.value
    store.collections.notifications = (store.collections.notifications ?? []).filter(n => n.id !== id)
    try {
      await removeNotification(id)
      void store.persistCollection('notifications')
    } catch {
      store.collections.notifications = prev as unknown as Record<string, unknown>[]
    }
  }

  function startPolling(intervalMs = 30000) {
    if (pollingStarted) return
    pollingStarted = true
    setInterval(() => {
      store.loadCollection('notifications', () => getAll(), { ttlMs: 60_000 }).catch(() => {})
    }, intervalMs)
  }

  onMounted(() => {
    refresh()
    startPolling()
  })

  return { notifications, loading, unreadCount, refresh, markRead, markAll, remove }
}
