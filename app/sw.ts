/// <reference lib="webworker" />

import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching'
import { NavigationRoute, registerRoute } from 'workbox-routing'

declare let self: ServiceWorkerGlobalScope & { __WB_MANIFEST: Array<{ url: string, revision?: string }> }

interface PushAction {
  action: string
  title: string
  icon?: string
}

interface SyncEventLike {
  tag: string
  waitUntil(promise: Promise<void>): void
}

cleanupOutdatedCaches()
precacheAndRoute(self.__WB_MANIFEST)

const navigationRoute = new NavigationRoute(
  createHandlerBoundToURL('/'),
  {
    denylist: [/^\/api\//]
  }
)
registerRoute(navigationRoute)

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting()
  }
})

self.addEventListener('push', (event) => {
  let payload: { title?: string, body?: string, url?: string, icon?: string, badge?: string, actions?: PushAction[] } = {}
  if (event.data) {
    try {
      payload = event.data.json()
    } catch {
      payload = { body: event.data.text() }
    }
  }

  const options: NotificationOptions & { actions?: PushAction[] } = {
    body: payload.body || '',
    icon: payload.icon || '/pwa-192x192.png',
    badge: payload.badge || '/pwa-192x192.png',
    data: { url: payload.url || '/' },
    actions: payload.actions
  }

  event.waitUntil(self.registration.showNotification(payload.title || 'TaskFlow', options))
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const targetUrl = (event.notification.data && event.notification.data.url) || '/'

  event.waitUntil(
    (async () => {
      const allClients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      for (const client of allClients) {
        if ('navigate' in client) {
          await client.navigate(targetUrl)
          return client.focus()
        }
      }
      return self.clients.openWindow(targetUrl)
    })()
  )
})

self.addEventListener('sync', ((event) => {
  const syncEvent = event as unknown as SyncEventLike
  if (syncEvent.tag === 'sync-outbox') {
    syncEvent.waitUntil(notifyClientsToSync())
  }
}) as EventListener)

async function notifyClientsToSync() {
  const allClients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
  for (const client of allClients) {
    client.postMessage({ type: 'SYNC_OUTBOX' })
  }
}

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim())
})
