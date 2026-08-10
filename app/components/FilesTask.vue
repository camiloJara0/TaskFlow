<script setup>
defineProps({
  files: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['removed'])

const downloading = ref(false)
const deleting = ref(false)

const getFileIcon = (tipo) => {
  if (!tipo) return 'i-lucide-file'

  if (tipo.includes('pdf')) return 'i-lucide-file-text'
  if (tipo.includes('word') || tipo.includes('document'))
    return 'i-lucide-file-text'

  if (tipo.includes('excel') || tipo.includes('spreadsheet'))
    return 'i-lucide-file-spreadsheet'

  if (tipo.includes('powerpoint') || tipo.includes('presentation'))
    return 'i-lucide-file-pie-chart'

  if (tipo.includes('image'))
    return 'i-lucide-image'

  if (tipo.includes('zip') || tipo.includes('rar'))
    return 'i-lucide-archive'

  if (tipo.includes('video'))
    return 'i-lucide-video'

  return 'i-lucide-file'
}

const getFileColor = (tipo) => {
  if (!tipo) return 'text-gray-500'

  if (tipo.includes('pdf')) return 'text-red-500'
  if (tipo.includes('word')) return 'text-blue-500'
  if (tipo.includes('excel')) return 'text-green-500'
  if (tipo.includes('powerpoint')) return 'text-orange-500'
  if (tipo.includes('image')) return 'text-purple-500'
  if (tipo.includes('zip')) return 'text-yellow-500'

  return 'text-gray-500'
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'

  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  let size = bytes

  while (size >= 1024 && i < units.length - 1) {
    size /= 1024
    i++
  }

  return `${size.toFixed(2)} ${units[i]}`
}

const getExtension = (file) => {
  return file.tipo?.split('/')[1]?.toUpperCase() || 'FILE'
}

const openFile = (url) => {
  window.open(url, '_blank')
}

const downloadFile = async (file) => {
  if (downloading.value) return
  downloading.value = true
  try {
    const tasks = useTasksService()
    const blob = await tasks.downloadFile(file.id)
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = file.nombre || 'archivo'
    link.click()
    window.URL.revokeObjectURL(url)
  } finally {
    downloading.value = false
  }
}

const removeFile = async (file) => {
  if (deleting.value) return
  deleting.value = true
  try {
    const tasks = useTasksService()
    await tasks.removeFile(file.id)
    emit('removed')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="space-y-3">
    <div
      v-for="file in files"
      :key="file.id"
      class="group flex items-center gap-4 p-4 rounded-2xl glass-card hover:shadow-lg hover:shadow-blue-500/10 hover:border-blue-400/40 transition-all duration-200"
    >
      <!-- Icono -->
      <div
        class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/70 dark:bg-white/5 border border-white/80 dark:border-white/10 shadow-sm"
      >
        <UIcon
          :name="getFileIcon(file.tipo)"
          class="w-6 h-6"
          :class="getFileColor(file.tipo)"
        />
      </div>

      <!-- Información -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <p class="font-medium truncate">
            {{ file.nombre }}
          </p>

          <UBadge
            color="neutral"
            variant="soft"
            size="xs"
          >
            {{ getExtension(file) }}
          </UBadge>
        </div>

        <div class="flex items-center gap-3 mt-1">
          <span class="text-xs text-muted">
            {{ formatSize(file.peso) }}
          </span>

          <span class="text-xs text-muted">
            {{ file.tipo }}
          </span>
        </div>
      </div>

      <!-- Acciones -->
      <div
        class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <UButton
          icon="i-lucide-eye"
          color="primary"
          variant="ghost"
          size="sm"
          @click="openFile(file.url)"
        />

        <UButton
          icon="i-lucide-download"
          color="neutral"
          variant="ghost"
          size="sm"
          :loading="downloading"
          @click="downloadFile(file)"
        />

        <UButton
          icon="i-lucide-trash-2"
          color="neutral"
          variant="ghost"
          size="sm"
          :loading="deleting"
          @click="removeFile(file)"
        />
      </div>
    </div>
  </div>
</template>
