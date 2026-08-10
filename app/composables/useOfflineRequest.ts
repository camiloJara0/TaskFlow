import type { ApiResponse } from '~/types/api'
import type { OfflineActionType, OfflineResource } from '~/types/offline'
import { isNetworkError } from '~/utils/network'

export function useOfflineRequest() {
  const store = useOfflineStore()
  const { request } = useApi()

  async function send<T>(options: {
    method: 'POST' | 'PUT' | 'DELETE'
    url: string
    body: Record<string, unknown>
    resource: OfflineResource
    type: OfflineActionType
  }): Promise<ApiResponse<T>> {
    if (store.isOffline) {
      await store.enqueue({
        type: options.type,
        resource: options.resource,
        method: options.method,
        url: options.url,
        body: options.body
      })
      return { success: true, data: null as unknown as T }
    }

    try {
      return await request<ApiResponse<T>>(options.url, { method: options.method, body: options.body })
    } catch (error) {
      if (isNetworkError(error)) {
        store.setOffline(true)
        await store.enqueue({
          type: options.type,
          resource: options.resource,
          method: options.method,
          url: options.url,
          body: options.body
        })
        return { success: true, data: null as unknown as T }
      }
      throw error
    }
  }

  return { send }
}
