<script setup lang="ts">
const props = defineProps<{
  comment: Record<string, any>
}>()

const emit = defineEmits<{ changed: [comment: any] }>()
const loading = ref(false)

const reactions: { tipo: string, label: string, value: string }[] = [
  { tipo: '👍', label: 'Me gusta', value: 'me gusta' },
  { tipo: '❤️', label: 'Me encanta', value: 'me encanta' },
  { tipo: '😂', label: 'Jaja', value: 'me divierte' },
  { tipo: '👏', label: 'Aplauso', value: 'gracias' }
]

const { user } = useApi()

const commentReactions = computed<any[]>(() => props.comment.reacciones || [])

async function toggleReaction(tipo: string) {
  if (loading.value) return
  loading.value = true
  try {
    const tasks = useTasksService()
    const reacciones = commentReactions.value
    const existing = reacciones.find(r => r.tipo === tipo && r.usuario?.id === user.value?.id)
    if (existing) {
      await tasks.removeReaction(existing.id)
    } else {
      await tasks.addReaction(props.comment.id, tipo)
    }
    emit('changed', props.comment)
  } finally {
    loading.value = false
  }
}

function countFor(tipo: string): number {
  return commentReactions.value.filter(r => r.tipo === tipo).length
}

function activeFor(tipo: string): boolean {
  return !!commentReactions.value.some(r => r.tipo === tipo && r.usuario?.id === user.value?.id)
}
</script>

<template>
  <div class="flex items-center gap-1 flex-wrap">
    <UButton
      v-for="r in reactions"
      :key="r.value"
      size="xs"
      color="neutral"
      :variant="activeFor(r.value) ? 'soft' : 'ghost'"
      :title="r.label"
      class="rounded-full px-1.5"
      :disabled="loading"
      @click="toggleReaction(r.value)"
    >
      {{ r.tipo }}
      <span
        v-if="countFor(r.value) > 0"
        class="text-[10px] font-semibold"
      >{{ countFor(r.value) }}</span>
    </UButton>
  </div>
</template>
