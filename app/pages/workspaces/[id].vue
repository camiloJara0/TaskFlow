<script setup lang="ts">
import MemberPicker from '~/components/forms/MemberPicker.vue'
const route = useRoute()
const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const viewMode = ref('list')
const showTaskForm = ref(false)
const bulkAssignPopover = ref(false)
const miembros = ref([])
const offlineStore = useOfflineStore()
const { getByWorkspace } = useTasksService()
const { getAll } = useWorkspacesService()
const { showSuccess } = useApi()

const workspaceId = computed(() => (typeof route.params.id === 'string' ? Number(route.params.id) : null))
const tasksKey = computed(() => (workspaceId.value ? `workspace-${workspaceId.value}-tasks` : 'workspace-tasks'))

const tasks = computed(() => (offlineStore.collections[tasksKey.value] ?? []) as Record<string, any>[])
const workspace = computed<Record<string, any>>(() => {
  const list = (offlineStore.collections.workspaces ?? []) as Record<string, any>[]
  return list.find(w => w.id === workspaceId.value) || { nombre: 'Proyecto', descripcion: '', equipo: { id: null, miembros: [] } }
})

onMounted(async () => {
  if (!workspaceId.value) return
  await offlineStore.loadCollection('workspaces', () => getAll())
  await offlineStore.loadCollection(tasksKey.value, () => getByWorkspace(workspaceId.value!))
})

const taskRefreshKey = useState('task-refresh-key', () => 0)

watch(taskRefreshKey, async () => {
  if (!workspaceId.value) return
  await offlineStore.loadCollection(tasksKey.value, () => getByWorkspace(workspaceId.value!), { force: true })
})

const membersOptions = computed(() => {
  if (!workspace.value.equipo.miembros) return

  return workspace.value.equipo.miembros.map((m: Record<string, any>) => {
    return {
      label: m.nombre,
      value: m.id
    }
  })
})

async function bulkAssign() {
  const teamsService = useTeamsService()
  const memberIds = Array.isArray(miembros.value)
    ? miembros.value
    : miembros.value
      ? [miembros.value]
      : []
  await teamsService.syncMembers(workspace.value.equipo.id, memberIds)

  showSuccess(`Espacio de trabajo compartido!`)

  bulkAssignPopover.value = false

}

async function handleSaved() {
  showTaskForm.value = false
  taskRefreshKey.value += 1
}
</script>

<template>
  <div class="p-6 sm:p-8 space-y-6 h-full flex flex-col page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4 shrink-0">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <NuxtLink
            to="/workspaces"
            class="text-sm text-muted hover:text-default transition-colors"
          >Espacios</NuxtLink>
          <UIcon
            name="i-lucide-chevron-right"
            class="w-3 h-3 text-muted"
          />
          <span class="text-sm font-heading font-semibold text-highlighted">{{ workspace?.nombre || 'Proyecto' }}</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          {{ workspace?.nombre || 'Proyecto' }}
        </h1>
        <p class="text-sm text-muted mt-1">
          {{ workspace?.descripcion }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <ViewSwitcher v-model="viewMode" />
        <UModal
          v-model:open="showTaskForm"
          :ui="{ content: 'glass-panel rounded-lg overflow-hidden' }"
        >
          <UButton
            label="Nueva tarea"
            icon="i-lucide-plus"
            size="sm"
            color="primary"
            variant="solid"
            class="rounded-xl shadow-lg shadow-blue-500/25"
          />
          <template #header>
            <div>
              <h3 class="text-base font-heading font-semibold">
                Nueva tarea
              </h3>
            </div>
          </template>
          <template #body>
            <div class="p-4">
              <formsTaskForm
                :status-options="[
                  { label: 'Pendiente', value: 'backlog' },
                  { label: 'Por Hacer', value: 'todo' },
                  { label: 'En progreso', value: 'in_progress' },
                  { label: 'Completado', value: 'done' }
                ]"
                @saved="handleSaved"
                :workspace="workspace.id"
                :user-options="membersOptions"
                @cancelled="showTaskForm = false"
              />
            </div>
          </template>
        </UModal>
      </div>
    </div>

    <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
      <UButton
        icon="i-lucide-filter"
        size="sm"
        color="neutral"
        variant="ghost"
        label="Filtros"
        class="rounded-xl"
      />
      <UButton
        icon="i-lucide-arrow-up-down"
        size="sm"
        color="neutral"
        variant="ghost"
        label="Ordenar"
        class="rounded-xl"
      />
        <UPopover
          v-model:open="bulkAssignPopover"
          :ui="{ content: 'glass-panel rounded-xl p-3 w-72' }"
        >
          <UButton
            icon="i-lucide-user-plus"
            size="sm"
            color="neutral"
            variant="ghost"
            title="Compartir a"
          />
          <template #content>
            <div class="space-y-2">
              <p class="text-xs font-medium">
                Compartir a
              </p>
              <MemberPicker v-model="miembros"/>
              <UButton
                label="Aplicar"
                size="sm"
                color="primary"
                variant="solid"
                class="w-full flex justify-center"
                @click="bulkAssign"
              />
            </div>
          </template>
        </UPopover>

      <div class="flex-1" />
      <div class="flex items-center -space-x-1.5">
        <UAvatar v-for="member in workspace?.equipo?.miembros"
          :text="member.nombre.charAt()"
          size="xs"
          class="ring-2 ring-white/80 dark:ring-white/10 rounded-full"
        />
        <div v-if="workspace?.equipo?.miembros.length > 3" class="w-6 h-6 rounded-full bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-[10px] font-bold text-blue-600 dark:text-blue-400">
          +{{ workspace?.equipo?.miembros.length - 3 }}
        </div>
      </div>
    </div>

    <div class="flex-1 min-h-0">
      <KanbanBoard
        v-if="viewMode === 'kanban'"
        :tasks="tasks"
        @open-task="(t) => emit('open-task', t)"
      />
      <CalendarView
        v-else-if="viewMode === 'calendar'"
        :tasks="tasks"
        @open-task="(t) => emit('open-task', t)"
      />
      <GanttView
        v-else-if="viewMode === 'gantt'"
        :tasks="tasks"
        @open-task="(t) => emit('open-task', t)"
      />
      <TimelineView
        v-else-if="viewMode === 'timeline'"
        :tasks="tasks"
        @open-task="(t) => emit('open-task', t)"
      />
      <div
        v-else
        class="glass-card rounded-lg overflow-hidden divide-y divide-default/70"
      >
        <div
          v-for="task in tasks"
          :key="task.id"
          class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/50 dark:hover:bg-white/4 transition-colors cursor-pointer group"
          @click="emit('open-task', task)"
        >
          <div
            class="w-4 h-4 rounded-full border-2"
            :class="statusKey(task.estado) === 'done' ? 'border-green-500 bg-green-500' : 'border-slate-300 dark:border-slate-600'"
          />
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium truncate"
              :class="{ 'line-through text-muted': statusKey(task.estado) === 'done' }"
            >
              {{ task.titulo }}
            </p>
            <p
              v-if="task.descripcion"
              class="text-xs text-muted truncate"
            >
              {{ task.descripcion }}
            </p>
          </div>
          <span
            class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
            :class="priorityBadgeClass(task.prioridad)"
          >
            {{ priorityLabel(task.prioridad) }}
          </span>
          <UBadge
            size="xs"
            :color="statusBadgeColor(task.estado)"
            variant="subtle"
          >
            {{ statusLabel(task.estado) }}
          </UBadge>
          <UAvatar
            :text="taskAssigneeInitials(task)"
            size="2xs"
            class="ring-2 ring-white/70 dark:ring-white/10 rounded-full"
          />
        </div>
        <div
          v-if="tasks.length === 0"
          class="p-12 text-center"
        >
          <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-inbox"
              class="w-6 h-6 text-muted"
            />
          </div>
          <p class="text-sm text-muted">
            No hay tareas en este espacio
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
