import { dbPut } from '~/utils/idb'

function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
  const rawData = window.atob(base64)
  const outputArray = new Uint8Array(new ArrayBuffer(rawData.length))
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i)
  }
  return outputArray
}

export function usePushNotifications() {
  const { registerPushToken, removePushToken } = useNotificationsService()
  const config = useRuntimeConfig()

  const supported = ref(false)
  const permission = ref<'granted' | 'denied' | 'default'>('default')
  const subscribed = ref(false)
  const busy = ref(false)
  const error = ref<string | null>(null)

  const isSupported = computed(() => supported.value)
  const isSubscribed = computed(() => subscribed.value)

  const vapidPublicKey = computed(() => String(config.public.vapidPublicKey || ''))

  function detectSupport() {
    if (typeof window === 'undefined') return
    const supportedApi = 'serviceWorker' in navigator && 'PushManager' in window
    supported.value = supportedApi
    permission.value = 'Notification' in window ? Notification.permission : 'default'
  }

  async function checkSubscription() {
    if (!supported.value || typeof navigator === 'undefined' || !('serviceWorker' in navigator)) return
    try {
      const sw = await navigator.serviceWorker.ready
      const sub = await sw.pushManager.getSubscription()
      subscribed.value = !!sub

      console.log(sub)
    } catch {
      subscribed.value = false
    }
  }

  async function subscribe(): Promise<boolean> {
    if (!supported.value || permission.value !== 'granted' || busy.value) return false
    busy.value = true
    error.value = null
    try {
      const sw = await navigator.serviceWorker.ready
      if (!sw.pushManager) return false

      let sub = await sw.pushManager.getSubscription()
      if (!sub) {
        if (!vapidPublicKey.value) {
          error.value = 'Clave pública VAPID no configurada'
          return false
        }
        sub = await sw.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(vapidPublicKey.value)
        })
      }

      subscribed.value = true
      await dbPut('kv', { key: 'pushEndpoint', value: sub.endpoint, updatedAt: Date.now() })
      try {
        await registerPushToken(sub.endpoint, navigator.userAgent)
      } catch {
        // el backend puede no estar disponible aún; la suscripción local sigue activa
      }
      return true
    } catch {
      error.value = 'No se pudo suscribir a notificaciones push'
      return false
    } finally {
      busy.value = false
    }
  }

  async function unsubscribe(): Promise<boolean> {
    if (!supported.value || busy.value) return false
    busy.value = true
    error.value = null
    try {
      const sw = await navigator.serviceWorker.ready
      const sub = await sw.pushManager.getSubscription()
      if (sub) {
        try {
          await removePushToken(sub.endpoint)
        } catch {
          // si el backend no responde, la suscripción local se elimina igualmente
        }
        await sub.unsubscribe()
      }
      subscribed.value = false
      await dbPut('kv', { key: 'pushEndpoint', value: null, updatedAt: Date.now() })
      return true
    } catch {
      error.value = 'No se pudo cancelar la suscripción'
      return false
    } finally {
      busy.value = false
    }
  }

  async function requestPermission(): Promise<boolean> {
    if (!supported.value || typeof Notification === 'undefined') return false
    const res = await Notification.requestPermission()
    permission.value = res
    if (res === 'granted') {
      return subscribe()
    }
    return false
  }

  async function toggle(enabled: boolean): Promise<boolean> {
    if (enabled) {
      if (permission.value !== 'granted') {
        const granted = await requestPermission()
        return granted
      }
      return subscribe()
    }
    return unsubscribe()
  }

  onMounted(async () => {
    detectSupport()
    if (supported.value) {
      await checkSubscription()
    }
  })

  return { supported: isSupported, subscribed: isSubscribed, permission, busy, error, requestPermission, subscribe, unsubscribe, toggle }
}
