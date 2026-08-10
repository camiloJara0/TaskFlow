<script setup lang="ts">
const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const stats = ref([
  { label: 'Tareas totales', value: '24', icon: 'i-lucide-list-check', color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-900/20' },
  { label: 'Completadas', value: '12', icon: 'i-lucide-check-circle', color: 'text-green-500', bg: 'bg-green-50 dark:bg-green-900/20' },
  { label: 'En progreso', value: '8', icon: 'i-lucide-loader', color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-900/20' },
  { label: 'Atrasadas', value: '4', icon: 'i-lucide-alert-circle', color: 'text-red-500', bg: 'bg-red-50 dark:bg-red-900/20' }
])

const recentTasks = ref([
  { id: 1, title: 'Rediseñar dashboard principal', project: 'Diseño UI/UX', priority: 'high', status: 'in_progress' },
  { id: 2, title: 'Implementar autenticación JWT', project: 'Backend API', priority: 'urgent', status: 'todo' },
  { id: 3, title: 'Crear componentes de formulario', project: 'Proyecto Alpha', priority: 'medium', status: 'done' },
  { id: 4, title: 'Optimizar consultas SQL', project: 'Backend API', priority: 'medium', status: 'in_progress' },
  { id: 5, title: 'Escribir tests unitarios', project: 'Proyecto Alpha', priority: 'low', status: 'backlog' }
])

const upcomingEvents = ref([
  { time: '10:00', title: 'Daily Standup', type: 'meeting' },
  { time: '14:00', title: 'Sprint Review', type: 'meeting' },
  { time: '16:30', title: 'Code Review', type: 'review' }
])

function statusBadge(status: string): { color: 'success' | 'info' | 'warning' | 'neutral', label: string } {
  const map: Record<string, { color: 'success' | 'info' | 'warning' | 'neutral', label: string }> = {
    backlog: { color: 'neutral', label: 'Pendiente' },
    todo: { color: 'info', label: 'Por hacer' },
    in_progress: { color: 'warning', label: 'En progreso' },
    done: { color: 'success', label: 'Completado' }
  }
  return map[status] || { color: 'neutral', label: status }
}

function priorityBadge(p: string) {
  const map: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    high: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
  }
  return map[p] || map.medium
}
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 page-enter">
    <div class="flex items-end justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Resumen</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Dashboard
        </h1>
        <p class="text-sm text-muted mt-1">
          Bienvenido de nuevo, Camilo. Aquí tienes un resumen de tu semana.
        </p>
      </div>
      <UButton
        icon="i-lucide-plus"
        label="Nueva tarea"
        size="sm"
        color="primary"
        variant="solid"
        class="rounded-xl shadow-lg shadow-blue-500/25"
      />
    </div>

    <GamificationHero />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="(stat, i) in stats"
        :key="stat.label"
        class="glass-card rounded-2xl p-5 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 cursor-pointer animate-fade-up"
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
        <div class="mt-3 h-1 rounded-full overflow-hidden bg-black/5 dark:bg-white/5">
          <div
            class="h-full rounded-full bg-linear-to-r from-blue-500 to-cyan-400"
            :style="{ width: `${25 * (i + 1)}%`, opacity: 0.6 }"
          />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="glass-card rounded-2xl overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-default">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-list-check"
                  class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"
                />
              </div>
              <h2 class="text-sm font-semibold">
                Tareas recientes
              </h2>
            </div>
            <UButton
              label="Ver todas"
              size="xs"
              color="neutral"
              variant="ghost"
              to="/tasks"
            />
          </div>
          <div class="divide-y divide-default/70">
            <div
              v-for="task in recentTasks"
              :key="task.id"
              class="flex items-center gap-3 px-5 py-3 hover:bg-white/50 dark:hover:bg-white/4 transition-colors cursor-pointer"
              @click="emit('open-task', task)"
            >
              <UCheckbox
                v-if="task.status === 'done'"
                :model-value="true"
              />
              <div
                v-else
                class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"
              />
              <div class="flex-1 min-w-0">
                <p
                  class="text-sm font-medium truncate"
                  :class="{ 'line-through text-muted': task.status === 'done' }"
                >
                  {{ task.title }}
                </p>
                <p class="text-xs text-muted">
                  {{ task.project }}
                </p>
              </div>
              <span
                class="text-[10px] font-medium px-2 py-0.5 rounded-full"
                :class="priorityBadge(task.priority)"
              >
                {{ task.priority === 'urgent' ? 'Urgente' : task.priority === 'high' ? 'Alta' : task.priority === 'medium' ? 'Media' : 'Baja' }}
              </span>
              <UBadge
                :color="(statusBadge(task.status).color as 'success' | 'info' | 'warning' | 'neutral')"
                size="xs"
                variant="subtle"
              >
                {{ statusBadge(task.status).label }}
              </UBadge>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="glass-card rounded-2xl overflow-hidden">
          <div class="px-5 py-4 border-b border-default">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-calendar-days"
                  class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"
                />
              </div>
              <h2 class="text-sm font-semibold">
                Próximos eventos
              </h2>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div
              v-for="event in upcomingEvents"
              :key="event.title"
              class="flex items-center gap-3"
            >
              <div class="w-10 shrink-0 text-center rounded-lg bg-white/60 dark:bg-white/5 py-1.5 border border-white/70 dark:border-white/10">
                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400">{{ event.time }}</span>
              </div>
              <div class="relative flex items-center justify-center w-6 shrink-0">
                <div
                  class="w-2 h-2 rounded-full"
                  :class="event.type === 'meeting' ? 'bg-blue-500' : 'bg-violet-500'"
                />
                <div
                  class="absolute w-4 h-4 rounded-full opacity-30 animate-pulse"
                  :class="event.type === 'meeting' ? 'bg-blue-500' : 'bg-violet-500'"
                />
              </div>
              <span class="text-sm font-medium text-default">{{ event.title }}</span>
            </div>
          </div>
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">
          <div class="px-5 py-4 border-b border-default">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-trending-up"
                  class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                />
              </div>
              <h2 class="text-sm font-semibold">
                Progreso semanal
              </h2>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-muted">Completado</span>
                <span class="font-semibold">68%</span>
              </div>
              <div class="h-2 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
                <div
                  class="h-full bg-linear-to-r from-blue-500 to-blue-600 rounded-full shadow-sm shadow-blue-500/40"
                  style="width: 68%"
                />
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-muted">En plazo</span>
                <span class="font-semibold">85%</span>
              </div>
              <div class="h-2 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
                <div
                  class="h-full bg-linear-to-r from-green-500 to-emerald-400 rounded-full shadow-sm shadow-green-500/40"
                  style="width: 85%"
                />
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-muted">Productividad</span>
                <span class="font-semibold">92%</span>
              </div>
              <div class="h-2 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
                <div
                  class="h-full bg-linear-to-r from-amber-500 to-orange-400 rounded-full shadow-sm shadow-amber-500/40"
                  style="width: 92%"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
