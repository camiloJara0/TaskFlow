export type OfflineActionType = 'create' | 'update' | 'delete'

export type OfflineResource = 'task' | 'workspace' | 'team' | 'notification'

export type OfflineStatus = 'pending' | 'syncing' | 'failed'

export interface OutboxItem {
  localId: string
  type: OfflineActionType
  resource: OfflineResource
  method: 'POST' | 'PUT' | 'DELETE'
  url: string
  body: Record<string, unknown>
  tempId?: string
  targetId?: number
  original?: Record<string, unknown>
  createdAt: string
  attempts: number
  status: OfflineStatus
  lastError?: string
}

export interface OutboxExport {
  app: 'taskflow'
  version: 1
  exportedAt: string
  items: OutboxItem[]
}

export const OFFLINE_COLLECTIONS = ['tasks', 'workspaces', 'teams', 'notifications'] as const

export type OfflineCollectionName = (typeof OFFLINE_COLLECTIONS)[number]

export const RESOURCE_COLLECTION: Record<OfflineResource, OfflineCollectionName> = {
  task: 'tasks',
  workspace: 'workspaces',
  team: 'teams',
  notification: 'notifications'
}

export const OFFLINE_COLLECTION_LABELS: Record<OfflineCollectionName, string> = {
  tasks: 'Tareas',
  workspaces: 'Espacios de trabajo',
  teams: 'Equipos',
  notifications: 'Notificaciones'
}
