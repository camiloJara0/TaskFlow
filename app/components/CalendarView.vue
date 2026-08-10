<script setup lang="ts">
const props = withDefaults(defineProps<{
  tasks?: Record<string, any>[]
  reunions?: Record<string, any>[]
}>(), {
  tasks: () => [],
  reunions: () => []
})

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
  'open-reunion': [reunion: Record<string, any>]
}>()

const now = new Date()
const currentMonth = ref(now.getMonth())
const currentYear = ref(now.getFullYear())

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

const todayFullLabel = now.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1).getDay()
  const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
  const days: (number | null)[] = []
  for (let i = 0; i < firstDay; i++) days.push(null)
  for (let d = 1; d <= daysInMonth; d++) days.push(d)
  while (days.length % 7 !== 0) days.push(null)
  return days
})

function prevMonth() {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

function nextMonth() {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

function goToday() {
  currentMonth.value = now.getMonth()
  currentYear.value = now.getFullYear()
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

function matchesReunionDay(value: unknown, day: number): boolean {
  const d = parseReunionDate(value)
  if (!d) return false
  return d.getDate() === day && d.getMonth() === currentMonth.value && d.getFullYear() === currentYear.value
}

function dayReunions(day: number | null): Record<string, any>[] {
  if (day === null) return []
  return props.reunions.filter(r => matchesReunionDay(r.fecha, day))
}

function isToday(day: number): boolean {
  return day === now.getDate() && currentMonth.value === now.getMonth() && currentYear.value === now.getFullYear()
}

function isWeekend(day: number): boolean {
  return new Date(currentYear.value, currentMonth.value, day).getDay() === 0 || new Date(currentYear.value, currentMonth.value, day).getDay() === 6
}

function taskChipClass(value: unknown): string {
  const map: Record<string, string> = {
    urgent: 'bg-red-500/10 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    high: 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    medium: 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    low: 'bg-gray-500/10 text-gray-600 dark:bg-white/8 dark:text-gray-400'
  }
  return map[priorityKey(value)] ?? map.medium ?? ''
}

function dayCount(day: number | null): number {
  if (day === null) return 0
  return dayTasks(day).length + dayReunions(day).length
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div class="flex items-center gap-1.5">
        <UButton
          icon="i-lucide-chevron-left"
          size="sm"
          color="neutral"
          variant="ghost"
          class="rounded-lg"
          @click="prevMonth"
        />
        <div class="min-w-40 px-1">
          <p class="text-lg font-heading font-bold leading-tight text-highlighted">
            {{ monthNames[currentMonth] }}
          </p>
          <p class="text-[10px] font-medium text-muted">
            {{ currentYear }}
          </p>
        </div>
        <UButton
          icon="i-lucide-chevron-right"
          size="sm"
          color="neutral"
          variant="ghost"
          class="rounded-lg"
          @click="nextMonth"
        />
      </div>

      <div class="flex items-center gap-3">
        <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-medium text-muted capitalize">
          <UIcon
            name="i-lucide-calendar-days"
            class="w-3.5 h-3.5"
          />
          {{ todayFullLabel }}
        </span>
        <UButton
          label="Hoy"
          size="xs"
          color="primary"
          variant="subtle"
          class="rounded-lg"
          @click="goToday"
        />
      </div>
    </div>

    <div class="flex items-center gap-3 flex-wrap text-[10px] font-medium text-muted px-1">
      <span class="inline-flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-red-500" />
        Urgente
      </span>
      <span class="inline-flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-amber-500" />
        Alta
      </span>
      <span class="inline-flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-blue-500" />
        Media
      </span>
      <span class="inline-flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-gray-400" />
        Baja
      </span>
      <span class="inline-flex items-center gap-1.5">
        <UIcon
          name="i-lucide-video"
          class="w-3 h-3 text-violet-500"
        />
        Reunión
      </span>
    </div>

    <div class="grid grid-cols-7 gap-1.5">
      <div
        v-for="day in dayNames"
        :key="day"
        class="text-center text-[10px] font-bold uppercase tracking-[0.12em] text-muted py-1"
      >
        {{ day }}
      </div>

      <div
        v-for="(day, idx) in calendarDays"
        :key="idx"
        class="min-h-28 md:rounded-lg rounded-sm border p-1.5 transition-colors"
        :class="day !== null
          ? isToday(day)
            ? 'border-blue-500/40 bg-blue-500/5 ring-1 ring-blue-500/20'
            : isWeekend(day)
              ? 'border-default/40 bg-white/30 dark:bg-white/3'
              : 'border-default/40 bg-white/50 dark:bg-white/4'
          : 'border-transparent'"
      >
        <template v-if="day">
          <div class="flex items-center justify-between mb-1">
            <span
              class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full"
              :class="isToday(day)
                ? 'bg-linear-to-br from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/30'
                : 'text-default'"
            >
              {{ day }}
            </span>
            <div class="flex items-center gap-1.5">
              <span
                v-if="dayStarts(day).length > 0"
                class="text-[9px] text-muted"
                title="Inician hoy"
              >
                ●
              </span>
              <span
                v-if="dayReunions(day).length > 0"
                class="text-[9px] text-violet-500"
                title="Reuniones"
              >
                <UIcon
                  name="i-lucide-video"
                  class="w-3 h-3"
                />
              </span>
            </div>
          </div>

          <div class="space-y-1">
            <button
              v-for="task in dayTasks(day)"
              :key="task.id"
              type="button"
              class="w-full text-left px-1.5 py-1 rounded-md text-[10px] font-medium truncate transition-all cursor-pointer"
              :class="[taskChipClass(task.prioridad), statusKey(task.estado) === 'done' ? 'line-through opacity-60' : '']"
              @click="emit('open-task', task)"
            >
              <span
                class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle"
                :class="priorityDotClass(task.prioridad)"
              />
              {{ task.titulo }}
            </button>
            <button
              v-for="reunion in dayReunions(day)"
              :key="`r-${reunion.id}`"
              type="button"
              class="w-full text-left px-1.5 py-1 rounded-md text-[10px] font-medium truncate transition-all cursor-pointer bg-violet-500/10 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300"
              @click="emit('open-reunion', reunion)"
            >
              <UIcon
                name="i-lucide-video"
                class="w-3 h-3 shrink-0 mr-0.5 align-middle"
              />
              <span class="font-semibold">{{ formatReunionTime(reunion.hora) }}</span>
              {{ reunion.titulo }}
            </button>
            <div
              v-if="dayCount(day) > 4"
              class="text-[9px] font-medium text-muted px-1"
            >
              +{{ dayCount(day) - 4 }} más
            </div>
          </div>
        </template>
      </div>
    </div>

    <div
      v-if="props.tasks.length === 0 && props.reunions.length === 0"
      class="text-center glass-card rounded-2xl p-8"
    >
      <div class="w-12 h-12 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-2">
        <UIcon
          name="i-lucide-calendar-x"
          class="w-5 h-5 text-muted"
        />
      </div>
      <p class="text-sm text-muted">
        No hay tareas ni reuniones con fechas para mostrar
      </p>
    </div>
  </div>
</template>
