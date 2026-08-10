<script setup lang="ts">
const props = defineProps<{
  taskId: number
}>()

const emit = defineEmits<{ saved: [comment: any] }>()
const loading = ref(false)

const label = ref('')

async function handleSubmit() {
  if (!label.value.trim()) return
  loading.value = true
  try {
    const tasks = useTasksService()
    const res = await tasks.addComment(props.taskId, label.value)
    label.value = ''
    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex gap-2">
    <UInput
      v-model="label"
      placeholder="Escribe nombre de etiqueta..."
      size="sm"
      class="flex-1"
      @keydown.enter.prevent="handleSubmit"
    />
    <UButton
      icon="i-lucide-send"
      size="sm"
      color="primary"
      variant="solid"
      :disabled="!label.trim() || loading"
      :loading="loading"
      @click="handleSubmit"
    />
  </div>
</template>
