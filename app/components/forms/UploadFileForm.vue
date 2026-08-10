<script setup lang="ts">
const props = defineProps<{
  taskId: number
}>()

const emit = defineEmits<{ saved: [comment: any] }>()
const loading = ref(false)

const nombre = ref('')
const file = ref<File | null>(null)

async function handleSubmit() {
  if (!nombre.value.trim()) return
  if (!file.value) return
  
  loading.value = true
  try {
    const tasks = useTasksService()
    const res = await tasks.uploadFile(props.taskId, file.value, nombre.value)
    nombre.value = ''
    file.value = null
    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-2">
    <UFileUpload
      v-model="file"
      label="Adjunta archivo a la tarea"
      size="sm"
      class="flex-1"
      @keydown.enter.prevent="handleSubmit"
    />
    <div class="flex gap-2">
        <UInput v-model="nombre" placeholder="Nombre" class="w-full"></UInput>
        <UButton
          icon="i-lucide-send"
          size="sm"
          color="primary"
          variant="solid"
          :disabled="!nombre.trim() || loading"
          :loading="loading"
          @click="handleSubmit"
        />
    </div>
  </div>
</template>
