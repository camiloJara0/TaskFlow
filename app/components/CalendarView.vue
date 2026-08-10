<script setup lang="ts">
const props = withDefaults(defineProps<{
  tasks?: Record<string, any>[]
}>(), {
  tasks: () => []
})

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const now = new Date()
const currentMonth = ref(now.getMonth())
const currentYear = ref(now.getFullYear())

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1).getDay()
  const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
  const days: (number | null)[] = []
  for (let i = 0; i < firstDay; i++) days.push(null)
  for (let d = 1; d <= daysInMonth; d++) days.push(d)
  return days
})

function prevMonth() {
  if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
  else currentMonth.value--
}

function nextMonth() {
  if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++ }
  else currentMonth.value++
}

function matchesDay(value: unknown, day: number): boolean {
  const d = parseTaskDate(value)
  if (!d) return false
  return d.getDate() === day && d.getMonth() === currentMonth.value && d.getFullYear() === currentYear.value
}

function dayTasks(day: number | null): Record<string, any>[] {
  if (day === null) return []
  return props.tasks.filter(t => matchesDay(t.fecha_vencimiento, day))
}

function dayStarts(day: number | null): Record<string, any>[] {
  if (day === null) return []
  return props.tasks.filter(t => matchesDay(t.fecha_inicio, day) && !matchesDay(t.fecha_vencimiento, day))
}

function isToday(day: number): boolean {
  return day === now.getDate() && currentMonth.value === now.getMonth() && currentYear.value === now.getFullYear()
}

function isWeekend(day: number): boolean {
  return new Date(currentYear.value, currentMonth.value, day).getDay() === 0 || new Date(currentYear.value, currentMonth.value, day).getDay() === 6
}

function taskChipClass(value: unknown): string {
  const map: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
    high: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'
  }
  return map[priorityKey(value)] || map.medium || 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <UButton icon="i-lucide-chevron-left" size="sm" color="neutral" variant="ghost" class="rounded-xl" @click="prevMonth" />
        <span class="text-lg font-heading font-semibold">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
        <UButton icon="i-lucide-chevron-right" size="sm" color="neutral" variant="ghost" class="rounded-xl" @click="nextMonth" />
      </div>
      <UButton label="Hoy" size="xs" color="neutral" variant="subtle" class="rounded-lg" @click="currentMonth = now.getMonth(); currentYear = now.getFullYear()" />
    </div>

    <div class="grid grid-cols-7 gap-1 rounded-xs overflow-hidden">
      <div
        v-for="day in dayNames"
        :key="day"
        class="bg-white/40 dark:bg-white/3 backdrop-blur-xl px-2 py-2.5 text-xs font-semibold text-muted text-center rounded"
      >
        {{ day }}
      </div>

      <div
        v-for="(day, idx) in calendarDays"
        :key="idx"
        class="glass-card rounded-lg min-h-25 p-2 m-0.5"
        :class="day !== null ? { 'bg-white/30! dark:bg-white/3!': isWeekend(day) } : ''"
      >
        <template v-if="day">
          <div class="flex items-center justify-between">
            <span
              class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full"
              :class="isToday(day)
                ? 'bg-linear-to-br from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/30'
                : 'text-default'"
            >
              {{ day }}
            </span>
            <span v-if="dayStarts(day).length > 0" class="text-[9px] font-medium text-muted" title="Inician hoy">
              ● {{ dayStarts(day).length }}
            </span>
          </div>

          <div class="mt-1.5 space-y-1">
            <div
              v-for="task in dayTasks(day)"
              :key="task.id"
              class="px-1.5 py-1 rounded-md text-[10px] font-medium truncate cursor-pointer hover:opacity-80 hover:-translate-x-0.5 transition-all shadow-sm"
              :class="taskChipClass(task.prioridad)"
              @click="emit('open-task', task)"
            >
              <span v-if="statusKey(task.estado) === 'done'" class="mr-0.5">✓</span>
              {{ task.titulo }}
            </div>
            <div v-if="dayTasks(day).length > 4" class="text-[9px] font-medium text-muted px-1">
              +{{ dayTasks(day).length - 4 }} más
            </div>
          </div>
        </template>
      </div>
    </div>

    <div v-if="props.tasks.length === 0" class="mt-4 text-center glass-card rounded-2xl p-6">
      <div class="w-12 h-12 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-2">
        <UIcon name="i-lucide-calendar-x" class="w-5 h-5 text-muted" />
      </div>
      <p class="text-sm text-muted">No hay tareas con fechas para mostrar</p>
    </div>
  </div>
</template>
