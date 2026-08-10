<script setup lang="ts">
const props = defineProps<{
  workspace?: Record<string, any>
  teamOptions?: { label: string; value: number }[]
}>()

const {getAll} = useTeamsService()
const emit = defineEmits<{ saved: [data: any]; cancelled: [] }>()
const loading = ref(false)
const teams = ref([])

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'nombre', label: 'Nombre', required: true, maxLength: 255 },
  { key: 'equipo_id', label: 'Equipo', required: true }
])

async function fetchTeams () {
  const resTeams = await getAll()
  teams.value = resTeams.data.map((t: any) => ({ label: t.nombre, value: t.id }))
}

onMounted(async() => {
  await fetchTeams()
  setForm({
    equipo_id: props.workspace?.equipo_id || null,
    nombre: props.workspace?.nombre || '',
    descripcion: props.workspace?.descripcion || '',
    color: props.workspace?.color || '#8B5CF6',
    icono: props.workspace?.icono || '',
    orden: props.workspace?.orden || 0,
    miembros: props.workspace?.miembros?.map((m: any) => m.usuario?.id ?? m.usuario_id) || []
  })
})

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const workspaces = useWorkspacesService()
    if (props.workspace?.id) {
      await workspaces.update(props.workspace.id, form.value)
    } else {
      await workspaces.create(form.value as any)
    }
    const equipoId = form.value.equipo_id
    if (equipoId) {
      const teams = useTeamsService()
      await teams.syncMembers(equipoId, form.value.miembros || [])
    }
    emit('saved', form.value)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="space-y-4">
    <UFormField label="Nombre del espacio" :error="visibleErrors.nombre || false">
      <UInput
        v-model="form.nombre"
        placeholder="Ej: Diseño UI/UX"
        size="lg"
        class="w-full"
        @blur="touch('nombre')"
        @update:model-value="touch('nombre')"
      />
    </UFormField>

    <UFormField label="Equipo" :error="visibleErrors.equipo_id || false">
      <USelect
        v-model="form.equipo_id"
        :items="teams || []"
        placeholder="Seleccionar equipo"
        class="w-full"
        size="sm"
        @update:model-value="touch('equipo_id')"
      />
    </UFormField>

    <UFormField label="Descripción">
      <UTextarea
        v-model="form.descripcion"
        placeholder="Descripción del espacio..."
        :rows="3"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Miembros">
      <MemberPicker v-model="form.miembros" />
    </UFormField>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Color">
        <div class="flex items-center gap-2">
          <UInput v-model="form.color" type="color" class="w-10 h-10 p-0.5 rounded" />
          <UInput v-model="form.color" placeholder="#8B5CF6" size="sm" class="flex-1" />
        </div>
      </UFormField>

      <UFormField label="Icono">
        <USelect
          :items="[
            {label: ': Carpeta', value: 'i-lucide-folder', icon: 'i-lucide-folder'},
            {label: ': Tecnologia', value: 'i-lucide-'}
          ]"
          v-model="form.icono"
          placeholder="i-lucide-folder"
          size="sm"
          class="w-full"
        />
      </UFormField>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <UButton
        type="submit"
        :label="workspace?.id ? 'Guardar cambios' : 'Crear espacio'"
        size="sm"
        color="primary"
        variant="solid"
        class="flex-1"
        :loading="loading"
        :disabled="loading"
      />
      <UButton
        label="Cancelar"
        size="sm"
        color="neutral"
        variant="ghost"
        @click="emit('cancelled')"
      />
    </div>
  </UForm>
</template>
