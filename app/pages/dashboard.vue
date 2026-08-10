<script setup lang="ts">
import type { Reunion, Task } from '~/types/api'

const emit = defineEmits<{
  'open-task': [task: Task]
}>()

const offlineStore = useOfflineStore()
const { getAll } = useTasksService()
const { reunions, refresh: refreshReunions } = useReunions()
const gamification = useGamification()

const tasks = computed(() => (offlineStore.collections.tasks ?? []) as unknown as Task[])

const showTaskForm = ref(false)
const showReunionForm = ref(false)

const stats = computed(() => {
  const all = tasks.value
  const done = all.filter(t => statusKey(t.estado) === 'done').length
  const inProgress = all.filter(t => statusKey(t.estado) === 'in_progress').length
  const overdue = all.filter((t) => {
    if (statusKey(t.estado) === 'done') return false
    const d = parseTaskDate(t.fecha_vencimiento)
    if (!d) return false
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return d.getTime() < today.getTime()
  }).length
  return [
    { label: 'Tareas totales', value: all.length, icon: 'i-lucide-list-check', color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-900/20' },
    { label: 'Completadas', value: done, icon: 'i-lucide-check-circle', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-900/20' },
    { label: 'En progreso', value: inProgress, icon: 'i-lucide-loader', color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-900/20' },
    { label: 'Atrasadas', value: overdue, icon: 'i-lucide-alert-circle', color: 'text-red-500', bg: 'bg-red-50 dark:bg-red-900/20' }
  ]
})

const isSameDay = (a: Date, b: Date): boolean => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()

const todayTasks = computed(() => {
  const today = new Date()
  return tasks.value.filter((t) => {
    if (statusKey(t.estado) === 'done') return false
    const d = parseTaskDate(t.fecha_vencimiento)
    return d ? isSameDay(d, today) : false
  })
})

const todayReunions = computed(() => {
  const today = new Date()
  return reunions.value.filter((r) => {
    const d = parseReunionDate(r.fecha)
    return d ? isSameDay(d, today) : false
  })
})

async function fetchTasks() {
  await offlineStore.loadCollection('tasks', () => getAll())
}

async function handleTaskSaved() {
  showTaskForm.value = false
  await fetchTasks()
}

async function handleReunionSaved() {
  showReunionForm.value = false
  await refreshReunions()
}

const taskRefreshKey = useState('task-refresh-key', () => 0)

watch(taskRefreshKey, () => {
  fetchTasks()
})

onMounted(async () => {
  await Promise.all([
    offlineStore.loadCollection('tasks', () => getAll()),
    offlineStore.loadCollection('reunions', () => useReunionsService().getAll())
  ])
})

function openReunion(reunion: Reunion) {
  navigateTo(`/reuniones/${reunion.id}`)
}
</script>

<template>
  <div class="p-6 sm:p-8 space-y-6 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span
            class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]"
          >Resumen</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Dashboard
        </h1>
        <p class="text-sm text-muted mt-1">
          Tu calendario, tu progreso y tu día en un solo lugar.
        </p>
      </div>
      <div class="flex items-center gap-2">
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
                  { label: 'Revisión', value: 'review' },
                  { label: 'Completado', value: 'done' }
                ]"
                @saved="handleTaskSaved"
                @cancelled="showTaskForm = false"
              />
            </div>
          </template>
        </UModal>
        <UModal
          v-model:open="showReunionForm"
          :ui="{ content: 'glass-panel rounded-lg overflow-hidden' }"
        >
          <UButton
            label="Nueva reunión"
            icon="i-lucide-video"
            size="sm"
            color="neutral"
            variant="subtle"
            class="rounded-xl"
          />
          <template #header>
            <div>
              <h3 class="text-base font-heading font-semibold">
                Nueva reunión
              </h3>
            </div>
          </template>
          <template #body>
            <div class="p-4">
              <formsReunionForm
                @saved="handleReunionSaved"
                @cancelled="showReunionForm = false"
              />
            </div>
          </template>
        </UModal>
      </div>
    </div>
    <div class="grid xl:grid-cols-[1fr_200px] grid-cols-1 gap-3">
      <div class="glass-card rounded-lg p-4 sm:p-5">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-3 px-1">
          <div>
            <h2 class="text-sm font-semibold flex items-center gap-2">
              <span class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-calendar-days"
                  class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"
                />
              </span>
              Calendario
            </h2>
            <p class="text-xs text-muted mt-0.5">
              Tareas, reuniones y fechas clave del mes
            </p>
          </div>
          <NuxtLink
            to="/tasks"
            class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
          >
            Ver todas las tareas
            <UIcon
              name="i-lucide-arrow-right"
              class="w-3.5 h-3.5"
            />
          </NuxtLink>
        </div>
        <CalendarView
          :tasks="tasks"
          :reunions="reunions"
          @open-task="(t) => emit('open-task', t as Task)"
          @open-reunion="(r) => openReunion(r as Reunion)"
        />
      </div>
      <div class="flex flex-col gap-4">
        <div
          v-for="(stat, i) in stats"
          :key="stat.label"
          class="glass-card rounded-lg p-5 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 animate-fade-up"
          :style="{ animationDelay: `${i * 60}ms` }"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md"
              :class="stat.bg"
            >
              <UIcon
                :name="stat.icon"
                class="w-5 h-5"
                :class="stat.color"
              />
            </div>
            <div>
              <p class="text-2xl font-heading font-bold tracking-tight text-highlighted">
                {{ stat.value }}
              </p>
              <p class="text-xs text-muted">
                {{ stat.label }}
              </p>
            </div>
          </div>
        </div>

        <div class="glass-card rounded-lg overflow-hidden h-fit">
          <div class="px-5 py-4 border-b border-default">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-trending-up"
                  class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"
                />
              </div>
              <h2 class="text-sm font-semibold">
                Mi progreso
              </h2>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-center gap-3">
              <GamificationPet :size="64" />
              <div class="min-w-0">
                <p class="text-sm font-heading font-bold text-highlighted">
                  Nivel {{ gamification.state.value.level }}
                </p>
                <p class="text-xs text-muted truncate">
                  {{ gamification.title.value.title }}
                </p>
              </div>
            </div>
            <div
              v-if="gamification.state.value.streak > 0"
              class="flex items-center gap-2 rounded-xl bg-amber-500/10 px-3 py-2"
            >
              <UIcon
                name="i-lucide-flame"
                class="w-4 h-4 text-amber-500"
              />
              <p class="text-xs font-semibold text-highlighted">
                {{ gamification.state.value.streak }} días de racha
              </p>
            </div>
            <NuxtLink
              to="/progress"
              class="flex items-center gap-2 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline pt-1"
            >
              Ver progreso completo
              <UIcon
                name="i-lucide-arrow-right"
                class="w-3.5 h-3.5"
              />
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="glass-card rounded-2xl overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-default">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-sun"
                  class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                />
              </div>
              <h2 class="text-sm font-semibold">
                Para hoy
              </h2>
            </div>
            <span class="text-xs text-muted">
              {{ todayTasks.length + todayReunions.length }} pendientes
            </span>
          </div>
          <div class="divide-y divide-default/70">
            <div
              v-for="task in todayTasks"
              :key="`t-${task.id}`"
              class="flex items-center gap-3 px-5 py-3 hover:bg-white/50 dark:hover:bg-white/4 transition-colors cursor-pointer"
              @click="emit('open-task', task)"
            >
              <span
                class="w-2 h-2 rounded-full shrink-0"
                :class="priorityDotClass(task.prioridad)"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">
                  {{ task.titulo }}
                </p>
                <p class="text-xs text-muted">
                  Vence hoy · {{ priorityLabel(task.prioridad) }}
                </p>
              </div>
              <UBadge
                :color="statusBadgeColor(task.estado)"
                size="xs"
                variant="subtle"
              >
                {{ statusLabel(task.estado) }}
              </UBadge>
            </div>
            <div
              v-for="reunion in todayReunions"
              :key="`r-${reunion.id}`"
              class="flex items-center gap-3 px-5 py-3 hover:bg-white/50 dark:hover:bg-white/4 transition-colors cursor-pointer"
              @click="openReunion(reunion)"
            >
              <span class="w-2 h-2 rounded-full shrink-0 bg-violet-500" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate flex items-center gap-1.5">
                  <UIcon
                    name="i-lucide-video"
                    class="w-3.5 h-3.5 text-violet-500"
                  />
                  {{ reunion.titulo }}
                </p>
                <p class="text-xs text-muted">
                  {{ formatReunionTime(reunion.hora) }} · Reunión
                </p>
              </div>
              <span
                class="text-[10px] font-medium px-2 py-0.5 rounded-full"
                :class="reunionEstadoChipClass(reunion.estado)"
              >
                {{ reunionEstadoLabel(reunion.estado) }}
              </span>
            </div>
            <div
              v-if="todayTasks.length === 0 && todayReunions.length === 0"
              class="p-10 text-center"
            >
              <div
                class="w-12 h-12 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-2"
              >
                <UIcon
                  name="i-lucide-party-popper"
                  class="w-5 h-5 text-emerald-500"
                />
              </div>
              <p class="text-sm text-muted">
                Nada pendiente para hoy. ¡Disfruta el día!
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <DashboardFloatingProgress />
  </div>
</template>
