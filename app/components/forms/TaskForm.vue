<script setup lang="ts">
const props = defineProps<{
  task?: Record<string, any>
  lista_id?: number
  estado_id?: number
  statusOptions?: { label: string, value: string | number }[]
  userOptions?: { label: string, value: number }[]
  workspace?: number
}>()

const emit = defineEmits<{ saved: [data: any], cancelled: [] }>()
const loading = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'titulo', label: 'Título', required: true, maxLength: 500 },
  { key: 'prioridad', label: 'Prioridad', required: false },
  { key: 'fecha_vencimiento', label: 'Fecha de vencimiento', required: false },
  { key: 'estimacion_horas', label: 'Estimación', required: false }
])

const prioridades = ['Urgente', 'Alta', 'Media', 'Baja']

onMounted(() => {
  const today = new Date().toISOString().split('T')[0]

  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)

  setForm({
    titulo: props.task?.titulo || '',
    descripcion: props.task?.descripcion || '',
    prioridad: props.task?.prioridad || 'Media',
    estado: props.task?.estado || 'backlog',
    responsable_id: props.task?.responsable_id || null,
    estimacion_horas: props.task?.estimacion_horas || null,
    etiquetas: props.task?.etiquetas?.map((e: any) => e.id) || [],
    fecha_inicio: props.task?.fecha_inicio || today,
    espacio_trabajo_id: props.workspace || null,
    fecha_vencimiento:
      props.task?.fecha_vencimiento || tomorrow.toISOString().split('T')[0]
  })

})

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const tasks = useTasksService()
    if (props.task?.id) {
      await tasks.update(props.task.id, form.value)
    } else {
      await tasks.create({ ...form.value, estado: 'backlog' } as any)
    }
    emit('saved', form.value)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" class="space-y-4" @submit="handleSubmit">
    <UFormField label="Título de la tarea" :error="visibleErrors.titulo || false">
      <UInput v-model="form.titulo" placeholder="¿Qué hay que hacer?" size="lg" class="w-full" @blur="touch('titulo')"
        @update:model-value="touch('titulo')" />
    </UFormField>

    <UFormField label="Descripción">
      <UTextarea v-model="form.descripcion" placeholder="Añade una descripción..." :rows="3" class="w-full" />
    </UFormField>

    <div v-if="props.workspace && props.workspace > 0">
      <UFormField label="Responsable">
        <USelect v-model="form.responsable_id" :items="userOptions || []" placeholder="Sin asignar" class="w-full"
          size="sm" />
      </UFormField>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Prioridad">
        <USelect v-model="form.prioridad" :items="prioridades" class="w-full" size="sm" />
      </UFormField>
      <UFormField label="Estimación (horas)">
        <UInputNumber v-model="form.estimacion_horas" :min="0" placeholder="0" size="sm" class="w-full" />
      </UFormField>
      <UFormField v-if="props.task" label="Estado">
        <USelect v-model="form.estado" :items="statusOptions || []" class="w-full" size="sm" />
      </UFormField>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Fecha de inicio">
        <!-- <UInputDate v-model="form.fecha_inicio" size="sm" class="w-full" /> -->
        <UInput v-model="form.fecha_inicio" type="date" size="sm" class="w-full" />
      </UFormField>

      <UFormField label="Fecha de vencimiento" :error="visibleErrors.fecha_vencimiento || false">
        <!-- <UInputDate v-model="form.fecha_vencimiento" size="sm" class="w-full" /> -->
        <UInput v-model="form.fecha_vencimiento" type="date" size="sm" class="w-full" />
      </UFormField>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
      <UButton label="Cancelar" size="sm" color="neutral" variant="ghost" @click="emit('cancelled')" class="px-10" />
      <UButton type="submit" :label="task?.id ? 'Guardar cambios' : 'Crear tarea'" size="sm" color="primary"
        variant="solid" class="flex-1 flex justify-center" :loading="loading" :disabled="loading" />
    </div>
  </UForm>
</template>
