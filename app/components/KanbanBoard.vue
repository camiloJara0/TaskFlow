<script setup lang="ts">
const props = withDefaults(defineProps<{
  tasks?: Record<string, any>[]
}>(), {
  tasks: () => []
})

const emit = defineEmits<{
  'open-task': [task: Record<string, any>]
}>()

const columnDefs = [
  { id: 'backlog', title: 'Pendiente', color: 'bg-gray-400' },
  { id: 'todo', title: 'Por hacer', color: 'bg-blue-500' },
  { id: 'in_progress', title: 'En progreso', color: 'bg-amber-500' },
  { id: 'review', title: 'Revisión', color: 'bg-violet-500' },
  { id: 'done', title: 'Completado', color: 'bg-green-500' }
]

const columns = computed(() => {
  return columnDefs.map((def) => ({
    ...def,
    tasks: props.tasks.filter(t => statusKey(t.estado) === def.id)
  }))
})

function columnTotalCount(): number {
  return props.tasks.length
}
</script>

<template>
  <div class="flex gap-4 h-full overflow-x-auto pb-4">
    <div
      v-for="col in columns"
      :key="col.id"
      class="shrink-0 w-72 flex flex-col glass-card rounded-lg p-3"
    >
      <div class="flex items-center gap-2 px-1 mb-3">
        <div class="w-2.5 h-2.5 rounded-full shadow-sm" :class="col.color" />
        <span class="text-sm font-heading font-semibold">{{ col.title }}</span>
        <span class="text-[11px] font-bold text-muted ml-auto bg-white/60 dark:bg-white/5 rounded-full w-6 h-6 flex items-center justify-center">
          {{ col.tasks.length }}
        </span>
      </div>

      <div class="flex-1 space-y-2.5 min-h-25">
        <div
          v-for="task in col.tasks"
          :key="task.id"
          class="p-3.5 bg-white/70 dark:bg-white/5 rounded-xl border border-white/80 dark:border-white/10 cursor-pointer hover:border-blue-400/50 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-200 group backdrop-blur-xl"
          draggable="true"
          @click="emit('open-task', task)"
        >
          <div class="flex items-start justify-between gap-2">
            <span class="text-sm font-medium leading-snug text-default">{{ task.titulo }}</span>
            <div v-if="task.subtareas?.length" class="flex items-center gap-1 shrink-0 text-[10px] text-muted mt-0.5">
              <UIcon name="i-lucide-list-checks" class="w-3 h-3" />
              {{ task.subtareas.length }}
            </div>
          </div>

          <p v-if="task.descripcion" class="text-xs text-muted truncate mt-1">{{ task.descripcion }}</p>

          <div class="mt-2.5 flex items-center gap-2">
            <span
              class="text-[10px] font-semibold px-2 py-0.5 rounded-full text-white shadow-sm"
              :class="prioritySolidClass(task.prioridad)"
            >
              {{ priorityLabel(task.prioridad) }}
            </span>
            <div class="flex-1" />

            <div
              v-if="task.porcentaje"
              class="hidden md:flex items-center gap-1 text-[10px] text-muted"
            >
              <div class="w-10 h-1 rounded-full bg-black/10 dark:bg-white/10 overflow-hidden">
                <div
                  class="h-full rounded-full bg-linear-to-r from-blue-500 to-cyan-400"
                  :style="{ width: `${taskProgress(task)}%` }"
                />
              </div>
              {{ taskProgress(task) }}%
            </div>

            <UAvatar
              :text="taskAssigneeInitials(task)"
              size="2xs"
              class="ring-2 ring-white/70 dark:ring-white/10 rounded-full"
            />
          </div>
        </div>

        <div
          v-if="col.tasks.length === 0"
          class="p-6 rounded-xl border-2 border-dashed border-slate-300/60 dark:border-white/10 flex flex-col items-center justify-center text-muted"
        >
          <UIcon name="i-lucide-inbox" class="w-5 h-5 mb-1.5 opacity-60" />
          <span class="text-[11px]">Sin tareas</span>
        </div>
      </div>
    </div>

    <div v-if="columnTotalCount() === 0" class="flex-1 flex items-center justify-center">
      <div class="text-center glass-card rounded-2xl p-10">
        <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
          <UIcon name="i-lucide-kanban" class="w-6 h-6 text-muted" />
        </div>
        <p class="text-sm font-medium">Aún no hay tareas</p>
        <p class="text-xs text-muted mt-1">Las tareas aparecerán aquí</p>
      </div>
    </div>
  </div>
</template>
