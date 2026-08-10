<script setup lang="ts">
import type { OutboxItem } from '~/types/offline'
import { RESOURCE_COLLECTION, OFFLINE_COLLECTION_LABELS } from '~/types/offline'

const store = useOfflineStore()

const fileInput = ref<HTMLInputElement | null>(null)

function openImport() {
  fileInput.value?.click()
}

const editModalOpen = ref(false)
const editingItem = ref<OutboxItem | null>(null)
const editBody = ref('')
const editError = ref('')
const importError = ref('')
const clearModalOpen = ref(false)

const intervals = [
  { label: '2 minutos', value: 2 },
  { label: '5 minutos', value: 5 },
  { label: '10 minutos', value: 10 },
  { label: '15 minutos', value: 15 }
]

const intervalMinutes = computed(() => Math.round(store.syncIntervalMs / 60000))

function typeMeta(item: OutboxItem): { label: string, icon: string, color: string, badge: 'success' | 'info' | 'error' } {
  if (item.type === 'create') return { label: 'Crear', icon: 'i-lucide-plus-circle', color: 'text-emerald-600 dark:text-emerald-400', badge: 'success' }
  if (item.type === 'update') return { label: 'Editar', icon: 'i-lucide-pencil', color: 'text-blue-600 dark:text-blue-400', badge: 'info' }
  return { label: 'Eliminar', icon: 'i-lucide-trash-2', color: 'text-rose-600 dark:text-rose-400', badge: 'error' }
}

function resourceLabel(item: OutboxItem): string {
  return OFFLINE_COLLECTION_LABELS[RESOURCE_COLLECTION[item.resource]]
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleString('es', { dateStyle: 'short', timeStyle: 'short' })
}

function formatLastSync(value: number | null): string {
  if (!value) return 'Nunca'
  return new Date(value).toLocaleString('es', { dateStyle: 'short', timeStyle: 'short' })
}

function bodyPreview(item: OutboxItem): string {
  const raw = JSON.stringify(item.body)
  return raw.length > 80 ? `${raw.slice(0, 80)}…` : raw
}

function openEdit(item: OutboxItem) {
  editingItem.value = item
  editBody.value = JSON.stringify(item.body, null, 2)
  editError.value = ''
  editModalOpen.value = true
}

function saveEdit() {
  if (!editingItem.value) return
  try {
    const parsed = JSON.parse(editBody.value)
    if (typeof parsed !== 'object' || parsed === null || Array.isArray(parsed)) {
      throw new Error('El body debe ser un objeto JSON')
    }
    void store.editPending(editingItem.value.localId, parsed)
    editModalOpen.value = false
  } catch (error) {
    editError.value = error instanceof Error ? error.message : 'JSON inválido'
  }
}

function onFileSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    importError.value = ''
    try {
      void store.importPending(JSON.parse(String(reader.result)))
    } catch (error) {
      importError.value = error instanceof Error ? error.message : 'No se pudo importar el archivo'
    }
    input.value = ''
  }
  reader.readAsText(file)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Estado -->
    <div class="glass-card rounded-lg p-5 space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center"
            :class="store.isOnline ? 'bg-emerald-500/10' : 'bg-amber-500/10'">
            <UIcon :name="store.isOnline ? 'i-lucide-wifi' : 'i-lucide-wifi-off'" class="w-5 h-5"
              :class="store.isOnline ? 'text-emerald-500' : 'text-amber-500'" />
          </div>
          <div>
            <p class="text-sm font-medium">
              {{ store.isOnline ? 'En línea' : 'Sin conexión' }}
            </p>
            <p class="text-xs text-muted">
              Última sincronización: {{ formatLastSync(store.lastSyncAt) }}
            </p>
          </div>
        </div>
        <UButton label="Sincronizar ahora" icon="i-lucide-refresh-cw" size="sm" color="primary" variant="solid"
          class="rounded-xl" :loading="store.syncing" :disabled="!store.isOnline || store.pendingCount === 0"
          @click="store.syncNow()" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white/50 dark:bg-white/5 border border-white/70 dark:border-white/10 p-4">
          <p class="text-2xl font-heading font-bold tracking-tight text-highlighted">
            {{ store.pendingCount }}
          </p>
          <p class="text-xs text-muted">
            Cambios pendientes de enviar
          </p>
        </div>
        <div class="rounded-xl bg-white/50 dark:bg-white/5 border border-white/70 dark:border-white/10 p-4">
          <p class="text-2xl font-heading font-bold tracking-tight text-highlighted">
            {{ Math.round(intervalMinutes) }}
          </p>
          <p class="text-xs text-muted">
            Minutos entre sincronizaciones
          </p>
        </div>
        <div
          class="rounded-xl bg-white/50 dark:bg-white/5 border border-white/70 dark:border-white/10 p-4 flex flex-col justify-between">
          <USelect :model-value="intervalMinutes" :items="intervals" size="sm" class="w-full"
            @update:model-value="store.setSyncInterval(Number($event))" />
          <p class="text-xs text-muted mt-2">
            El store refresca los datos de la API automáticamente
          </p>
        </div>
      </div>
    </div>

    <p v-if="importError" class="text-xs text-rose-600 dark:text-rose-400">
      {{ importError }}
    </p>

    <!-- Cola de pendientes -->
    <div class="glass-card rounded-lg overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-default">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center">
            <UIcon name="i-lucide-send" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
          </div>
          <h2 class="text-sm font-semibold">
            Cambios pendientes
          </h2>
          <UBadge v-if="store.pendingCount > 0" size="xs" variant="soft"
            class="w-6 h-6 rounded-full text-xs flex justify-center items-center" color="warning">
            {{ store.pendingCount }}
          </UBadge>
        </div>
        <div class="flex items-center gap-1.5">
          <UButton label="Exportar" icon="i-lucide-download" size="xs" color="neutral" variant="ghost"
            class="rounded-lg" :disabled="store.pendingCount === 0" @click="store.exportPending()" />
          <UButton label="Importar" icon="i-lucide-upload" size="xs" color="neutral" variant="ghost" class="rounded-lg"
            @click="openImport" />
          <input ref="fileInput" type="file" accept="application/json,.json" class="hidden" @change="onFileSelected">
          <UButton label="Vaciar" icon="i-lucide-trash-2" size="xs" color="error" variant="ghost" class="rounded-lg"
            :disabled="store.pendingCount === 0" @click="clearModalOpen = true" />
        </div>
      </div>

      <div v-if="store.pendingCount === 0" class="p-10 text-center">
        <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
          <UIcon name="i-lucide-check-check" class="w-6 h-6 text-muted" />
        </div>
        <p class="text-sm text-muted">
          No hay cambios pendientes. Todo está sincronizado.
        </p>
      </div>

      <div v-else class="divide-y divide-default/70">
        <div v-for="item in store.pending" :key="item.localId"
          class="px-5 py-3.5 hover:bg-white/50 dark:hover:bg-white/4 transition-colors">
          <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3 min-w-0">
              <UIcon :name="typeMeta(item).icon" class="w-4 h-4 shrink-0" :class="typeMeta(item).color" />
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <UBadge size="xs" :color="typeMeta(item).badge" variant="subtle">
                    {{ typeMeta(item).label }}
                  </UBadge>
                  <UBadge size="xs" color="neutral" variant="subtle">
                    {{ resourceLabel(item) }}
                  </UBadge>
                  <span class="text-[10px] font-mono text-muted">
                    {{ item.method }} {{ item.url }}
                  </span>
                </div>
                <p class="text-xs text-muted mt-1 font-mono truncate">
                  {{ bodyPreview(item) }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] text-muted shrink-0">
                {{ formatDate(item.createdAt) }}
              </span>
              <UBadge size="xs"
                :color="item.status === 'failed' ? 'error' : item.status === 'syncing' ? 'info' : 'neutral'"
                variant="subtle">
                {{ item.status === 'failed' ? 'Error' : item.status === 'syncing' ? 'Enviando…' : item.attempts > 0 ?
                  `Intento ${item.attempts}` : 'Pendiente' }}
              </UBadge>
              <div class="flex items-center gap-1">
                <UButton icon="i-lucide-square-pen" size="xs" color="neutral" variant="ghost" title="Editar"
                  @click="openEdit(item)" />
                <UButton icon="i-lucide-circle-play" size="xs" color="neutral" variant="ghost" title="Reintentar"
                  :disabled="!store.isOnline" @click="store.syncNow(item.localId)" />
                <UButton icon="i-lucide-x" size="xs" color="error" variant="ghost" title="Eliminar de la cola"
                  @click="store.removePending(item.localId)" />
              </div>
            </div>
          </div>
          <p v-if="item.lastError" class="text-[10px] text-rose-600 dark:text-rose-400 mt-2">
            {{ item.lastError }}
          </p>
        </div>
      </div>
    </div>

    <!-- Editar pendiente -->
    <UModal v-model:open="editModalOpen">
      <template #header>
        <h3 class="text-base font-heading font-semibold">
          Editar cambio pendiente
        </h3>
      </template>
      <template #body>
        <div class="p-4 space-y-3">
          <p class="text-xs text-muted break-all">
            {{ editingItem?.method }} {{ editingItem?.url }}
          </p>
          <UTextarea v-model="editBody" :rows="10" class="font-mono text-xs w-full" :ui="{ root: 'rounded-lg' }" />
          <p v-if="editError" class="text-xs text-rose-600 dark:text-rose-400">
            {{ editError }}
          </p>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton label="Cancelar" size="sm" color="neutral" variant="ghost" @click="editModalOpen = false" />
          <UButton label="Guardar" size="sm" color="primary" variant="solid" @click="saveEdit" />
        </div>
      </template>
    </UModal>

    <!-- Confirmar vaciar -->
    <UModal v-model:open="clearModalOpen">
      <template #header>
        <h3 class="text-base font-heading font-semibold">
          Vaciar cola de sincronización
        </h3>
      </template>
      <template #body>
        <p class="text-sm text-muted">
          Se eliminarán {{ store.pendingCount }} cambios pendientes. Esta acción no se puede deshacer.
        </p>
      </template>
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton label="Cancelar" size="sm" color="neutral" variant="ghost" @click="clearModalOpen = false" />
          <UButton label="Vaciar" size="sm" color="error" variant="solid"
            @click="store.clearPending(); clearModalOpen = false" />
        </div>
      </template>
    </UModal>
  </div>
</template>
