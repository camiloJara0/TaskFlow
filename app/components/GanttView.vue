<script setup lang="ts">
const props = withDefaults(defineProps<{
  tasks?: Record<string, any>[]
}>(), {
  tasks: () => []
})

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const DAY_MS = 86_400_000

interface GanttTask {
  task: Record<string, any>
  start: Date | null
  end: Date | null
}

const rows = computed<GanttTask[]>(() => {
  return props.tasks.map(task => ({
    task,
    start: parseTaskDate(task.fecha_inicio),
    end: parseTaskDate(task.fecha_vencimiento)
  }))
})

const datedRows = computed(() => rows.value.filter(r => r.start || r.end))

const timelineStart = computed(() => {
  const starts = datedRows.value.map(r => r.start).filter(Boolean) as Date[]
  const ends = datedRows.value.map(r => r.end).filter(Boolean) as Date[]
  const all = [...starts, ...ends]
  if (all.length === 0) return null
  return new Date(Math.min(...all.map(d => d.getTime())))
})

const timelineEnd = computed(() => {
  const starts = datedRows.value.map(r => r.start).filter(Boolean) as Date[]
  const ends = datedRows.value.map(r => r.end).filter(Boolean) as Date[]
  const all = [...starts, ...ends]
  if (all.length === 0) return null
  return new Date(Math.max(...all.map(d => d.getTime())))
})

const totalDays = computed(() => {
  if (!timelineStart.value || !timelineEnd.value) return 0
  return Math.max(Math.ceil((timelineEnd.value.getTime() - timelineStart.value.getTime()) / DAY_MS), 1) + 1
})

const dayColumns = computed(() => Array.from({ length: totalDays.value }, (_, i) => i))

function dayOffset(date: Date | null): number {
  if (!date || !timelineStart.value) return 0
  return Math.max(0, Math.floor((date.getTime() - timelineStart.value.getTime()) / DAY_MS))
}

function barStyle(row: GanttTask) {
  const start = row.start || row.end || timelineStart.value!
  const end = row.end || row.start || timelineStart.value!
  const left = (dayOffset(start) / totalDays.value) * 100
  const width = ((dayOffset(end) - dayOffset(start) + 1) / totalDays.value) * 100
  return { left: `${left}%`, width: `${Math.max(width, 2)}%` }
}

function dayLabel(index: number): string {
  if (!timelineStart.value) return String(index + 1)
  const d = new Date(timelineStart.value.getTime() + index * DAY_MS)
  return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })
}

function isWeekend(index: number): boolean {
  if (!timelineStart.value) return false
  const d = new Date(timelineStart.value.getTime() + index * DAY_MS)
  return d.getDay() === 0 || d.getDay() === 6
}
</script>

<template>
  <div class="overflow-x-auto glass-card rounded-lg p-4">
    <div v-if="rows.length === 0" class="text-center p-10">
      <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
        <UIcon name="i-lucide-chart-bar" class="w-6 h-6 text-muted" />
      </div>
      <p class="text-sm text-muted">No hay tareas para mostrar en Gantt</p>
    </div>

    <div v-else class="min-w-150">
      <div class="flex border-b border-default">
        <div class="w-48 shrink-0 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted">Tarea</div>
        <div class="flex-1 flex">
          <div
            v-for="d in dayColumns"
            :key="d"
            class="flex-1 text-[9px] text-muted text-center py-2 border-l border-default"
            :class="{ 'bg-blue-500/4 dark:bg-white/3': isWeekend(d) }"
          >
            {{ dayLabel(d) }}
          </div>
        </div>
      </div>

      <div
        v-for="row in rows"
        :key="row.task.id"
        class="flex items-center border-b border-default hover:bg-white/40 dark:hover:bg-white/3 transition-colors group"
      >
        <div class="w-48 shrink-0 px-3 py-3 flex items-center gap-2">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ row.task.titulo }}</p>
            <p class="text-[10px] text-muted flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full inline-block" :class="priorityDotClass(row.task.prioridad)" />
              {{ priorityLabel(row.task.prioridad) }}
              <span v-if="taskAssigneeName(row.task) !== '—'" class="truncate">· {{ taskAssigneeName(row.task) }}</span>
            </p>
          </div>
          <UAvatar
            v-if="taskAssigneeName(row.task) !== '—'"
            :text="taskAssigneeInitials(row.task)"
            size="2xs"
            class="shrink-0 ring-2 ring-white/70 dark:ring-white/10 rounded-full"
          />
        </div>

        <div class="flex-1 relative h-10 py-2">
          <template v-if="row.start || row.end">
            <div class="relative h-full">
              <div
                class="absolute top-1/2 -translate-y-1/2 h-6 rounded-lg flex items-center px-2 transition-all duration-200 group-hover:shadow-lg group-hover:shadow-blue-500/20 cursor-pointer overflow-hidden"
                :class="prioritySolidClass(row.task.prioridad)"
                :style="barStyle(row)"
                @click="emit('open-task', row.task)"
              >
                <div
                  class="h-full rounded-lg opacity-30 bg-white"
                  :style="{ width: `${taskProgress(row.task)}%` }"
                />
                <span v-if="taskProgress(row.task) > 0" class="text-[10px] text-white font-semibold ml-1 whitespace-nowrap">
                  {{ taskProgress(row.task) }}%
                </span>
              </div>
            </div>
          </template>
          <div v-else class="flex items-center h-full px-2">
            <span class="text-[10px] text-muted italic">Sin fechas asignadas</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
