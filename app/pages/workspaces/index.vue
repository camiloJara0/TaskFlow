<script setup lang="ts">
import TeamWorkspaceForm from '~/components/forms/TeamWorkspaceForm.vue'

const offlineStore = useOfflineStore()
const workspaces = computed(() => (offlineStore.collections.workspaces ?? []) as any[])

const showWorkspaceForm = ref(false)

const { getAll } = useWorkspacesService()

async function fetchWorkspaces() {
  await offlineStore.loadCollection('workspaces', () => getAll(), { force: true })
}

onMounted(async () => {
  await offlineStore.loadCollection('workspaces', () => getAll())
})

function handleSaved() {
  showWorkspaceForm.value = false
  fetchWorkspaces()
}

function getProgress(tareas: any[] = []) {
  if (!tareas.length) return 0

  const realizadas = tareas.filter(
    t => t.estado === 'done'
  ).length

  return Math.round((realizadas / tareas.length) * 100)
}
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Colaboración</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Espacios de trabajo
        </h1>
        <p class="text-sm text-muted mt-1">
          {{ workspaces.length }} espacios activos
        </p>
      </div>
      <div class="flex gap-2">
        <UModal v-model:open="showWorkspaceForm">
          <UButton
            label="Nuevo espacio"
            icon="i-lucide-plus"
            size="sm"
            color="primary"
            variant="solid"
            class="rounded-xl shadow-lg shadow-blue-500/25"
          />
          <template #header>
            <h3 class="text-lg font-semibold text-highlighted">
              Crear nuevo espacio de trabajo
            </h3>
          </template>
          <template #body>
            <TeamWorkspaceForm @saved="handleSaved" @cancelled="showWorkspaceForm = false" />
          </template>
        </UModal>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <NuxtLink
        v-for="(ws, i) in workspaces"
        :key="ws.id"
        :to="`/workspaces/${ws.id}`"
        class="glass-card rounded-2xl p-5 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 group animate-fade-up"
        :style="{ animationDelay: `${i * 60}ms` }"
      >
        <div class="relative mb-4">
          <!-- <div
            class="absolute -inset-0.5 rounded-2xl opacity-0 group-hover:opacity-100 blur-lg transition-opacity"
            :class="ws.color"
          /> -->
          <div class="relative flex items-center gap-3">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-lg"
              :style="{ backgroundColor: ws.color }"
            >
              {{ ws.nombre.charAt(0) }}
            </div>
            <div class="min-w-0">
              <h3 class="text-sm font-heading font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">{{ ws.nombre }}</h3>
              <p class="text-xs text-muted truncate">{{ ws.descripcion }}</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4 text-xs text-muted mb-4">
          <div class="flex items-center gap-1.5">
            <UIcon
              name="i-lucide-list-check"
              class="w-3.5 h-3.5"
            />
            <span>{{ ws.tareas.length || 0 }} tareas</span>
          </div>
          <div class="flex items-center gap-1.5">
            <UIcon
              name="i-lucide-users"
              class="w-3.5 h-3.5"
            />
            <span>{{ ws.equipo?.miembros?.length || 0 }} miembros</span>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="text-muted">Progreso</span>
            <span class="font-semibold">{{ getProgress(ws.tareas) }}%</span>
          </div>
          <div class="h-2 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500 shadow-sm"
              :style="{ width: `${getProgress(ws.tareas)}%`, backgroundColor: ws.color }"
            />
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
