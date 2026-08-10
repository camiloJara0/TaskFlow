<script setup lang="ts">
const props = withDefaults(defineProps<{
  tasks?: Record<string, any>[]
}>(), {
  tasks: () => []
})

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const timelineItems = computed(() => {
  return [...props.tasks]
    .map(task => ({
      task,
      date: parseTaskDate(task.fecha_inicio) || parseTaskDate(task.fecha_vencimiento) || parseTaskDate(task.created_at)
    }))
    .sort((a, b) => {
      if (!a.date && !b.date) return 0
      if (!a.date) return 1
      if (!b.date) return -1
      return a.date.getTime() - b.date.getTime()
    })
})

function statusVisual(task: Record<string, any>) {
  const key = statusKey(task.estado)
  if (key === 'done') {
    return { icon: 'i-lucide-check-circle', bg: 'bg-green-100 dark:bg-green-500/20', color: 'text-green-600 dark:text-green-400' }
  }
  if (key === 'in_progress') {
    return { icon: 'i-lucide-loader', bg: 'bg-blue-100 dark:bg-blue-500/20', color: 'text-blue-600 dark:text-blue-400' }
  }
  if (key === 'review') {
    return { icon: 'i-lucide-eye', bg: 'bg-violet-100 dark:bg-violet-500/20', color: 'text-violet-600 dark:text-violet-400' }
  }
  if (key === 'todo') {
    return { icon: 'i-lucide-circle-dashed', bg: 'bg-amber-100 dark:bg-amber-500/20', color: 'text-amber-600 dark:text-amber-400' }
  }
  return { icon: 'i-lucide-circle', bg: 'bg-slate-100 dark:bg-white/5', color: 'text-slate-400 dark:text-slate-500' }
}

function formatTimelineDate(date: Date | null): string {
  if (!date) return 'Sin fecha'
  return date.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short' })
}
</script>

<template>
  <div v-if="timelineItems.length === 0" class="glass-card rounded-2xl p-10 text-center">
    <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
      <UIcon name="i-lucide-chart-line" class="w-6 h-6 text-muted" />
    </div>
    <p class="text-sm text-muted">No hay tareas para mostrar en el timeline</p>
  </div>

  <div v-else class="relative glass-card rounded-lg p-6">
    <div class="absolute left-5.5 top-6 bottom-6 w-px bg-linear-to-b from-blue-500/30 via-slate-300/40 to-transparent dark:via-white/10" />

    <div class="space-y-0">
      <div
        v-for="(item, idx) in timelineItems"
        :key="item.task.id"
        class="relative flex gap-4 pb-6 last:pb-0 cursor-pointer group animate-fade-up"
        :style="{ animationDelay: `${Math.min(idx * 50, 400)}ms` }"
        @click="emit('open-task', item.task)"
      >
        <div class="relative z-10 flex items-center justify-center w-9 h-9 shrink-0">
          <div
            class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 border border-white/60 dark:border-white/10 shadow-sm group-hover:scale-110"
            :class="statusVisual(item.task).bg"
          >
            <UIcon :name="statusVisual(item.task).icon" class="w-4 h-4" :class="statusVisual(item.task).color" />
          </div>
        </div>

        <div class="flex-1 min-w-0 pt-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-medium text-muted">{{ formatTimelineDate(item.date) }}</span>
            <span
              class="text-[10px] font-semibold px-2 py-0.5 rounded-full text-white shadow-sm"
              :class="prioritySolidClass(item.task.prioridad)"
            >
              {{ priorityLabel(item.task.prioridad) }}
            </span>
            <UBadge size="xs" variant="subtle" :color="statusBadgeColor(item.task.estado)">
              {{ statusLabel(item.task.estado) }}
            </UBadge>
          </div>

          <p class="text-sm font-heading font-semibold mt-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ item.task.titulo }}
          </p>
          <p v-if="item.task.descripcion" class="text-sm text-muted mt-0.5 line-clamp-1">{{ item.task.descripcion }}</p>

          <div v-if="taskProgress(item.task) > 0" class="flex items-center gap-2 mt-2 max-w-xs">
            <div class="h-1.5 flex-1 rounded-full bg-black/5 dark:bg-white/10 overflow-hidden">
              <div
                class="h-full rounded-full bg-linear-to-r from-blue-500 to-cyan-400"
                :style="{ width: `${taskProgress(item.task)}%` }"
              />
            </div>
            <span class="text-[10px] font-semibold text-muted">{{ taskProgress(item.task) }}%</span>
          </div>

          <div v-if="taskAssigneeName(item.task) !== '—'" class="flex items-center gap-1.5 mt-2">
            <UAvatar :text="taskAssigneeInitials(item.task)" size="2xs" class="ring-2 ring-white/70 dark:ring-white/10 rounded-full" />
            <span class="text-[11px] text-muted">{{ taskAssigneeName(item.task) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
