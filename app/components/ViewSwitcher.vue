<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue?: string
}>(), {
  modelValue: 'list'
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const views = [
  { id: 'list', label: 'Lista', icon: 'i-lucide-list' },
  { id: 'kanban', label: 'Kanban', icon: 'i-lucide-columns-3' },
  { id: 'calendar', label: 'Calendario', icon: 'i-lucide-calendar' },
  { id: 'gantt', label: 'Gantt', icon: 'i-lucide-chart-bar' },
  { id: 'timeline', label: 'Timeline', icon: 'i-lucide-chart-line' }
]

const currentView = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})
</script>

<template>
  <div class="flex items-center gap-0.5 p-0.5 bg-(--ui-tertiary) rounded-lg">
    <UButton
      v-for="view in views"
      :key="view.id"
      :icon="view.icon"
      size="xs"
      color="neutral"
      :variant="currentView === view.id ? 'solid' : 'ghost'"
      class="rounded-md"
      @click="currentView = view.id"
    >
      <span class="hidden sm:inline ml-1.5">{{ view.label }}</span>
    </UButton>
  </div>
</template>
