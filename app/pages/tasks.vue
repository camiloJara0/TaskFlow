<script setup lang="ts">
import MemberPicker from '~/components/forms/MemberPicker.vue'
import ReminderForm from '~/components/forms/ReminderForm.vue'

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const viewMode = ref('list')
const searchQuery = ref('')
const filterPriority = ref('all')
const showTaskForm = ref(false)
const offlineStore = useOfflineStore()
const tasks = computed(() => (offlineStore.collections.tasks ?? []) as Record<string, any>[])
const { getAll } = useTasksService()
const { showSuccess } = useApi()

async function fetchTasks() {
  await offlineStore.loadCollection('tasks', () => getAll(), { force: true })
}

onMounted(async () => {
  await offlineStore.loadCollection('tasks', () => getAll())
})

const taskRefreshKey = useState('task-refresh-key', () => 0)

watch(taskRefreshKey, () => {
  fetchTasks()
})

const selectedTasks = ref<number[]>([])

function toggleSelect(id: number) {
  const idx = selectedTasks.value.indexOf(id)
  if (idx === -1) selectedTasks.value.push(id)
  else selectedTasks.value.splice(idx, 1)
}

function selectAll() {
  if (selectedTasks.value.length === filteredTasks.value.length) {
    selectedTasks.value = []
  } else {
    selectedTasks.value = filteredTasks.value.map(t => t.id)
  }
}

const filteredTasks = computed(() => {
  let result = tasks.value
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(t => (t.titulo || '').toLowerCase().includes(q))
  }
  if (filterPriority.value !== 'all') {
    result = result.filter(t => priorityKey(t.prioridad) === filterPriority.value)
  }
  return result
})

async function handleSaved() {
  showTaskForm.value = false
  await fetchTasks()
}

const statusItems = [
  { label: 'Pendiente', value: 'backlog' },
  { label: 'Por hacer', value: 'todo' },
  { label: 'En progreso', value: 'in_progress' },
  { label: 'Revisión', value: 'review' },
  { label: 'Completado', value: 'done' }
]

const priorityItems = ['Urgente', 'Alta', 'Media', 'Baja']

async function quickChangeStatus(task: Record<string, any>, estado: string) {
  const tasks = useTasksService()
  await tasks.update(task.id, { estado })
  const wasDone = statusKey(task.estado) === 'done'
  task.estado = estado
  const isDone = statusKey(estado) === 'done'
  if (!wasDone && isDone) {
    useGamification().registerTaskCompletion(task)
  }
  showSuccess('Estado actualizado')
}

async function quickChangePriority(task: Record<string, any>, prioridad: string) {
  const tasks = useTasksService()
  await tasks.update(task.id, { prioridad })
  task.prioridad = prioridad
  showSuccess('Prioridad actualizada')
}

async function quickArchive(task: Record<string, any>) {
  const tasks = useTasksService()
  await tasks.update(task.id, { archivada: !task.archivada })
  task.archivada = !task.archivada
  showSuccess(task.archivada ? 'Tarea archivada' : 'Tarea restaurada')
}

const deleteTaskModal = ref(false)
const taskToDelete = ref<Record<string, any> | null>(null)

function askDelete(task: Record<string, any>) {
  taskToDelete.value = task
  deleteTaskModal.value = true
}

async function confirmDelete() {
  if (!taskToDelete.value) return
  const tasks = useTasksService()
  await tasks.remove(taskToDelete.value.id)
  showSuccess('Tarea eliminada')
  deleteTaskModal.value = false
  taskToDelete.value = null
  await fetchTasks()
}

const assignTarget = ref<Record<string, any> | null>(null)
const assignValue = ref<number | null>(null)
const assignOpenId = ref<number | null>(null)

const reminderOpenId = ref<number | null>(null)

async function confirmAssign() {
  if (!assignTarget.value || !assignValue.value) return
  const tasks = useTasksService()
  await tasks.assignResponsible(assignTarget.value.id, assignValue.value)
  assignTarget.value.responsable_id = assignValue.value
  showSuccess('Responsable asignado')
  assignOpenId.value = null
}

function onAssignOpen(open: boolean, task: Record<string, any>) {
  if (open) {
    assignOpenId.value = task.id
    assignTarget.value = task
    assignValue.value = task.responsable_id || null
  } else {
    assignOpenId.value = null
  }
}

async function bulkArchive(archivada = true) {
  const tasks = useTasksService()
  for (const id of selectedTasks.value) {
    await tasks.update(id, { archivada })
  }
  showSuccess(`${selectedTasks.value.length} tareas ${archivada ? 'archivadas' : 'restauradas'}`)
  selectedTasks.value = []
  await fetchTasks()
}

const bulkAssignPopover = ref(false)
const bulkAssignValue = ref<number | null>(null)

async function bulkAssign() {
  if (!bulkAssignValue.value) return
  const tasks = useTasksService()
  for (const id of selectedTasks.value) {
    await tasks.assignResponsible(id, bulkAssignValue.value)
  }
  showSuccess(`${selectedTasks.value.length} tareas asignadas`)
  selectedTasks.value = []
  bulkAssignPopover.value = false
  await fetchTasks()
}

const bulkDeleteModal = ref(false)

async function confirmBulkDelete() {
  const tasks = useTasksService()
  for (const id of selectedTasks.value) {
    await tasks.remove(id)
  }
  showSuccess(`${selectedTasks.value.length} tareas eliminadas`)
  selectedTasks.value = []
  bulkDeleteModal.value = false
  await fetchTasks()
}
</script>

<template>
  <div class="p-6 sm:p-8 space-y-6 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Planificación</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Mis tareas
        </h1>
        <p class="text-sm text-muted mt-1">
          {{ filteredTasks.length }} tareas {{ filterPriority !== 'all' ? 'filtradas' : '' }}
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
                @saved="handleSaved" @cancelled="showTaskForm = false"
              />
            </div>
          </template>
        </UModal>
      </div>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
      <UInput
        v-model="searchQuery"
        placeholder="Buscar tareas..."
        size="sm"
        class="max-w-xs"
        leading
        :ui="{
          root: 'rounded-xl glass-chip',
          leading: 'text-muted'
        }"
      >
        <template #leading>
          <UIcon
            name="i-lucide-search"
            class="w-4 h-4 text-muted"
          />
        </template>
      </UInput>
      <USelect
        v-model="filterPriority"
        size="sm"
        :items="[
          { label: 'Todas las prioridades', value: 'all' },
          { label: 'Urgente', value: 'urgent' },
          { label: 'Alta', value: 'high' },
          { label: 'Media', value: 'medium' },
          { label: 'Baja', value: 'low' }
        ]"
        class="w-44"
      />
      <div
        v-if="selectedTasks.length > 0"
        class="flex items-center gap-2 ml-auto glass-chip rounded-xl px-2 py-1"
      >
        <span class="text-xs text-muted">{{ selectedTasks.length }} seleccionadas</span>
        <UPopover
          v-model:open="bulkAssignPopover"
          :ui="{ content: 'glass-panel rounded-xl p-3 w-72' }"
        >
          <UButton
            icon="i-lucide-user-plus"
            size="sm"
            color="neutral"
            variant="ghost"
            title="Asignar responsable"
          />
          <template #content>
            <div class="space-y-2">
              <p class="text-xs font-medium">
                Asignar responsable
              </p>
              <MemberPicker
                v-model="bulkAssignValue"
                :multiple="false"
              />
              <UButton
                label="Aplicar"
                size="sm"
                color="primary"
                variant="solid"
                class="w-full"
                :disabled="!bulkAssignValue"
                @click="bulkAssign"
              />
            </div>
          </template>
        </UPopover>
        <UButton
          icon="i-lucide-archive"
          size="sm"
          color="neutral"
          variant="ghost"
          title="Archivar"
          @click="bulkArchive(true)"
        />
        <UButton
          icon="i-lucide-trash-2"
          size="sm"
          color="error"
          variant="ghost"
          title="Eliminar"
          @click="bulkDeleteModal = true"
        />
      </div>
    </div>

    <div
      v-if="viewMode === 'list'"
      class="glass-card rounded-lg overflow-hidden"
    >
      <div class="flex items-center gap-3 px-5 py-3 border-b border-default bg-white/40 dark:bg-white/3 text-xs font-semibold text-muted uppercase tracking-wider">
        <UCheckbox
          :model-value="selectedTasks.length === filteredTasks.length && filteredTasks.length > 0"
          @click="selectAll"
        />
        <span class="flex-1">Tarea</span>
        <span class="w-20 text-center hidden md:block">Prioridad</span>
        <span class="w-24 text-center hidden lg:block">Estado</span>
        <span class="w-24 text-center hidden lg:block">Vencimiento</span>
        <span class="w-32 text-center hidden md:block">Acciones</span>
      </div>
      <div class="divide-y divide-default/70">
        <div
          v-for="task in filteredTasks"
          :key="task.id"
          class="flex items-center gap-3 px-5 py-3 hover:bg-white/50 dark:hover:bg-white/4 transition-colors cursor-pointer group"
          @click="emit('open-task', task)"
        >
          <UCheckbox
            :model-value="selectedTasks.includes(task.id)"
            @click.stop="toggleSelect(task.id)"
          />
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium truncate"
              :class="{ 'line-through text-muted': statusKey(task.estado) === 'done' }"
            >
              {{ task.titulo }}
            </p>
          </div>
          <span class="w-20 text-center hidden md:block">
            <span
              class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
              :class="priorityBadgeClass(task.prioridad)"
            >
              {{ priorityLabel(task.prioridad) }}
            </span>
          </span>
          <span class="w-24 text-center hidden lg:block">
            <UBadge
              :color="statusBadgeColor(task.estado)"
              size="xs"
              variant="subtle"
            >
              {{ statusLabel(task.estado) }}
            </UBadge>
          </span>
          <span class="w-24 text-xs text-muted text-center hidden lg:block">{{ formatShortDate(task.fecha_vencimiento) }}</span>
          <div
            class="w-32 items-center justify-end gap-1 hidden md:flex"
            @click.stop
          >
            <UPopover
              :ui="{ content: 'glass-panel rounded-xl p-3 w-72' }"
              @update:open="(open) => onAssignOpen(open, task)"
            >
              <UButton
                icon="i-lucide-user-plus"
                size="xs"
                color="neutral"
                variant="ghost"
                title="Asignar responsable"
              />
              <template #content>
                <div class="space-y-2">
                  <p class="text-xs font-medium">
                    Asignar responsable
                  </p>
                  <MemberPicker
                    v-model="assignValue"
                    :multiple="false"
                  />
                  <UButton
                    label="Aplicar"
                    size="sm"
                    color="primary"
                    variant="solid"
                    class="w-full"
                    :disabled="!assignValue"
                    @click="confirmAssign"
                  />
                </div>
              </template>
            </UPopover>
            <UPopover
              :open="reminderOpenId === task.id"
              :ui="{ content: 'glass-panel rounded-xl p-3 w-80' }"
              @update:open="(open) => { reminderOpenId = open ? task.id : null }"
            >
              <UButton
                icon="i-lucide-bell"
                size="xs"
                color="neutral"
                variant="ghost"
                title="Añadir recordatorio"
              />
              <template #content>
                <div class="space-y-2">
                  <p class="text-xs font-medium">
                    Añadir recordatorio
                  </p>
                  <ReminderForm
                    :task-id="task.id"
                    @saved="showSuccess('Recordatorio creado')"
                  />
                </div>
              </template>
            </UPopover>
            <UDropdownMenu
              :items="[
                [
                  {
                    label: 'Cambiar estado',
                    icon: 'i-lucide-arrow-right-left',
                    children: statusItems.map(s => ({
                      label: s.label,
                      onSelect: () => quickChangeStatus(task, s.value)
                    }))
                  }
                ],
                [
                  {
                    label: 'Cambiar prioridad',
                    icon: 'i-lucide-flame',
                    children: priorityItems.map(p => ({
                      label: p,
                      onSelect: () => quickChangePriority(task, p)
                    }))
                  }
                ],
                [
                  {
                    label: task.archivada ? 'Restaurar' : 'Archivar',
                    icon: 'i-lucide-archive',
                    onSelect: () => quickArchive(task)
                  },
                  {
                    label: 'Eliminar',
                    icon: 'i-lucide-trash-2',
                    color: 'error',
                    onSelect: () => askDelete(task)
                  }
                ]
              ]"
              :content="{ align: 'end' }"
            >
              <UButton
                icon="i-lucide-more-horizontal"
                size="xs"
                color="neutral"
                variant="ghost"
              />
            </UDropdownMenu>
          </div>
          <UAvatar
            :text="taskAssigneeInitials(task)"
            size="2xs"
            class="w-8 shrink-0 ring-2 ring-white/70 dark:ring-white/10 rounded-full md:hidden"
          />
        </div>
        <div
          v-if="filteredTasks.length === 0"
          class="p-12 text-center"
        >
          <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
            <UIcon
              name="i-lucide-inbox"
              class="w-6 h-6 text-muted"
            />
          </div>
          <p class="text-sm text-muted">
            No se encontraron tareas
          </p>
        </div>
      </div>
    </div>

    <KanbanBoard
      v-else-if="viewMode === 'kanban'"
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

    <UModal v-model:open="deleteTaskModal">
      <template #header>
        <h3 class="text-base font-heading font-semibold">
          Eliminar tarea
        </h3>
      </template>
      <template #body>
        <p class="text-sm text-muted">
          ¿Estás seguro de que deseas eliminar la tarea "{{ taskToDelete?.titulo }}"? Esta acción no se puede deshacer.
        </p>
      </template>
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton
            label="Cancelar"
            size="sm"
            color="neutral"
            variant="ghost"
            @click="deleteTaskModal = false"
          />
          <UButton
            label="Eliminar"
            size="sm"
            color="error"
            variant="solid"
            @click="confirmDelete"
          />
        </div>
      </template>
    </UModal>

    <UModal v-model:open="bulkDeleteModal">
      <template #header>
        <h3 class="text-base font-heading font-semibold">
          Eliminar tareas
        </h3>
      </template>
      <template #body>
        <p class="text-sm text-muted">
          ¿Estás seguro de que deseas eliminar {{ selectedTasks.length }} tareas? Esta acción no se puede deshacer.
        </p>
      </template>
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton
            label="Cancelar"
            size="sm"
            color="neutral"
            variant="ghost"
            @click="bulkDeleteModal = false"
          />
          <UButton
            label="Eliminar"
            size="sm"
            color="error"
            variant="solid"
            @click="confirmBulkDelete"
          />
        </div>
      </template>
    </UModal>
  </div>
</template>
