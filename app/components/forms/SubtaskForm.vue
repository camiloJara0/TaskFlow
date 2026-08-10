<script setup lang="ts">
const props = defineProps<{
  taskId: number
}>()

const emit = defineEmits<{ saved: [comment: any] }>()
const loading = ref(false)

const titulo = ref('')

async function handleSubmit() {
  if (!titulo.value.trim()) return
  loading.value = true
  try {
    const tasks = useTasksService()
    const res = await tasks.addSubtask(props.taskId, {titulo: titulo.value,})
    titulo.value = ''
    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex gap-2">
    <UInput
      v-model="titulo"
      placeholder="Titulo..."
      size="sm"
      class="flex-1"
      @keydown.enter.prevent="handleSubmit"
    />
    <UButton
      icon="i-lucide-send"
      size="sm"
      color="primary"
      variant="solid"
      :disabled="!titulo.trim() || loading"
      :loading="loading"
      @click="handleSubmit"
    />
  </div>
</template>
