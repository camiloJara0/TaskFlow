<script setup lang="ts">
import type { Reunion, ReunionIntegrante, Workspace } from '~/types/api'
import MemberPicker from './MemberPicker.vue'

const props = withDefaults(defineProps<{
  reunion?: Reunion
  workspaceId?: number | null
}>(), {
  reunion: undefined,
  workspaceId: null
})

const emit = defineEmits<{ saved: [data: Record<string, unknown>], cancelled: [] }>()
const loading = ref(false)
const offlineStore = useOfflineStore()
const { getAll: getWorkspaces } = useWorkspacesService()

const workspaces = computed(() => (offlineStore.collections.workspaces ?? []) as unknown as Workspace[])
const workspaceOptions = computed(() =>
  workspaces.value.map(w => ({ label: w.nombre, value: w.id }))
)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'titulo', label: 'Título', required: true, maxLength: 255 },
  { key: 'fecha', label: 'Fecha', required: true },
  { key: 'hora', label: 'Hora', required: true }
])

const estadoItems = [
  { label: 'Pendiente', value: 'pendiente' },
  { label: 'Confirmada', value: 'confirmada' },
  { label: 'En curso', value: 'en_curso' },
  { label: 'Finalizada', value: 'finalizada' },
  { label: 'Cancelada', value: 'cancelada' }
]

onMounted(async () => {
  await offlineStore.loadCollection('workspaces', () => getWorkspaces())
  const r = props.reunion
  setForm({
    espacio_trabajo_id: r?.espacio_trabajo_id ?? props.workspaceId ?? '',
    titulo: r?.titulo || '',
    descripcion: r?.descripcion || '',
    estado: r?.estado || 'pendiente',
    fecha: r?.fecha || '',
    hora: formatReunionTime(r?.hora) || '',
    url: r?.url || '',
    archivada: r?.archivada ?? false,
    integrantes: r?.integrantes?.map((i: ReunionIntegrante) => i.usuario?.id ?? i.usuario_id) || []
  })
})

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const reunions = useReunionsService()
    const payload = {
      espacio_trabajo_id: form.value.espacio_trabajo_id || null,
      titulo: form.value.titulo,
      descripcion: form.value.descripcion || null,
      estado: form.value.estado,
      fecha: form.value.fecha,
      hora: form.value.hora,
      url: form.value.url || null,
      archivada: form.value.archivada,
      integrantes: form.value.integrantes
    }
    if (props.reunion?.id) {
      await reunions.update(props.reunion.id, payload)
    } else {
      await reunions.create(payload)
    }
    emit('saved', form.value)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm
    :state="form"
    class="space-y-4"
    @submit="handleSubmit"
  >
    <UFormField
      label="Título"
      :error="visibleErrors.titulo || false"
    >
      <UInput
        v-model="form.titulo"
        placeholder="Ej: Sprint planning"
        size="lg"
        class="w-full"
        @blur="touch('titulo')"
        @update:model-value="touch('titulo')"
      />
    </UFormField>

    <UFormField label="Espacio de trabajo (opcional)">
      <USelect
        v-model="form.espacio_trabajo_id"
        :items="workspaceOptions"
        placeholder="Sin espacio de trabajo"
        size="lg"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Descripción">
      <UTextarea
        v-model="form.descripcion"
        placeholder="¿De qué tratará la reunión?"
        :rows="3"
        class="w-full"
      />
    </UFormField>

    <div class="grid grid-cols-2 gap-3">
      <UFormField
        label="Fecha"
        :error="visibleErrors.fecha || false"
      >
        <UInput
          v-model="form.fecha"
          type="date"
          size="lg"
          class="w-full"
          @blur="touch('fecha')"
          @update:model-value="touch('fecha')"
        />
      </UFormField>

      <UFormField
        label="Hora"
        :error="visibleErrors.hora || false"
      >
        <UInput
          v-model="form.hora"
          type="time"
          size="lg"
          class="w-full"
          @blur="touch('hora')"
          @update:model-value="touch('hora')"
        />
      </UFormField>
    </div>

    <UFormField label="Enlace de la videollamada">
      <UInput
        v-model="form.url"
        placeholder="https://meet.google.com/..."
        size="sm"
        class="w-full"
      />
    </UFormField>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Estado">
        <USelect
          v-model="form.estado"
          :items="estadoItems"
          size="sm"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Integrantes">
        <MemberPicker v-model="form.integrantes" />
      </UFormField>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <UButton
        label="Cancelar"
        size="sm"
        color="neutral"
        variant="ghost"
        class="px-10"
        @click="emit('cancelled')"
      />
      <UButton
        type="submit"
        :label="reunion?.id ? 'Guardar cambios' : 'Crear reunión'"
        size="sm"
        color="primary"
        variant="solid"
        class="flex-1 flex justify-center"
        :loading="loading"
        :disabled="loading"
      />
    </div>
  </UForm>
</template>
