<script setup lang="ts">
const props = defineProps<{
  taskId: number
}>()

const emit = defineEmits<{ saved: [reminder: any] }>()
const loading = ref(false)

const presets = [
  { label: 'En 5 minutos', tipo: '5_minutos', minutos: 5 },
  { label: 'En 30 minutos', tipo: '30_minutos', minutos: 30 },
  { label: 'En 1 hora', tipo: '1_hora', minutos: 60 },
  { label: 'En 1 día', tipo: '1_dia', minutos: 1440 },
  { label: 'En 1 semana', tipo: '1_semana', minutos: 10080 }
]

async function addReminder(preset: { tipo: string, minutos: number }) {
  if (loading.value) return
  loading.value = true
  try {
    const tasks = useTasksService()
    const fecha = new Date(Date.now() + preset.minutos * 60_000).toISOString()
    const res = await tasks.addReminder(props.taskId, { fecha, tipo: preset.tipo })
    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <UButton
      v-for="preset in presets"
      :key="preset.tipo"
      :label="preset.label"
      size="xs"
      color="neutral"
      variant="soft"
      icon="i-lucide-bell"
      class="rounded-lg"
      :disabled="loading"
      @click="addReminder(preset)"
    />
  </div>
</template>
