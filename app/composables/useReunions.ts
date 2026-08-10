import type { Reunion } from '~/types/api'

export function useReunions() {
  const offlineStore = useOfflineStore()
  const { getAll } = useReunionsService()

  const reunions = computed(() => (offlineStore.collections.reunions ?? []) as unknown as Reunion[])
  const loading = computed(() => offlineStore.loading.reunions ?? false)

  async function refresh(workspaceId?: number, force = false) {
    await offlineStore.loadCollection('reunions', () => getAll(workspaceId), { force })
  }

  function byWorkspace(workspaceId: number) {
    return computed(() => (offlineStore.collections[`workspace-${workspaceId}-reunions`] ?? []) as unknown as Reunion[])
  }

  async function loadByWorkspace(workspaceId: number, force = false) {
    await offlineStore.loadCollection(`workspace-${workspaceId}-reunions`, () => getAll(workspaceId), { force })
  }

  return { reunions, loading, refresh, byWorkspace, loadByWorkspace }
}
