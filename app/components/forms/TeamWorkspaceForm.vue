<script setup lang="ts">
import MemberPicker from './MemberPicker.vue'

const emit = defineEmits<{ saved: [data: any], cancelled: [] }>()
const loading = ref(false)
const teams = ref<{ label: string, value: number }[]>([])
const mode = ref<'existing' | 'new'>('existing')
const miembros = ref([])

const { getAll } = useTeamsService()

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'equipo_id', label: 'Equipo', required: false },
  { key: 'nombre', label: 'Nombre', required: true, maxLength: 255 }
])

onMounted(async () => {
  const res = await getAll()
  teams.value = res.data.map((t: any) => ({ label: t.nombre, value: t.id }))
  if (teams.value.length === 0) mode.value = 'new'
  setForm({
    equipo_id: teams.value[0]?.value ?? null,
    nombre: '',
    descripcion: '',
    color: '#8B5CF6',
    icono: '',
    orden: 0,
    teamNombre: '',
    teamDescripcion: '',
    teamIcono: '',
    teamColor: '#6366F1',
  })
})

async function handleSubmit() {
  if (mode.value === 'new' && !form.value.teamNombre?.trim()) {
    touch('equipo_id')
    return
  }
  if (!validate()) return
  loading.value = true
  try {
    const teamsService = useTeamsService()
    const workspacesService = useWorkspacesService()

    let equipoId = form.value.equipo_id
    if (mode.value === 'new') {
      const res = await teamsService.create({
        nombre: form.value.teamNombre,
        descripcion: form.value.teamDescripcion || undefined,
        icono: form.value.teamIcono || undefined,
        color: form.value.teamColor || undefined
      })
      equipoId = res.data?.id
    }

    const res = await workspacesService.create({
      equipo_id: equipoId,
      nombre: form.value.nombre,
      descripcion: form.value.descripcion || undefined,
      color: form.value.color || undefined,
      icono: form.value.icono || undefined,
      orden: form.value.orden || 0
    })

    const memberIds = Array.isArray(miembros.value)
      ? miembros.value
      : miembros.value
        ? [miembros.value]
        : []

    if (equipoId && memberIds?.length) {
      await teamsService.syncMembers(equipoId, memberIds)
    }

    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" class="space-y-4" @submit="handleSubmit">
    <UFormField label="Equipo" :error="visibleErrors.equipo_id || false">
      <div class="flex gap-2">
        <USelect v-if="teams.length > 0" v-model="mode" :items="[
          { label: 'Usar equipo existente', value: 'existing' },
          { label: 'Crear nuevo equipo', value: 'new' }
        ]" size="sm" class="w-44" />
        <USelect v-if="mode === 'existing'" v-model="form.equipo_id" :items="teams" placeholder="Seleccionar equipo"
          class="w-full" size="sm" @update:model-value="touch('equipo_id')" />
      </div>
    </UFormField>

    <div v-if="mode === 'new'" class="space-y-4 border border-default/70 rounded-lg p-4">
      <UFormField label="Nombre del equipo">
        <UInput v-model="form.teamNombre" placeholder="Ej: Equipo de diseño" size="sm" class="w-full" />
      </UFormField>

      <UFormField label="Descripción del equipo">
        <UTextarea v-model="form.teamDescripcion" placeholder="¿Para qué es este equipo?" :rows="2" class="w-full" />
      </UFormField>

      <div class="grid grid-cols-2 gap-3">
        <UFormField label="Color del equipo">
          <div class="flex items-center gap-2">
            <UInput v-model="form.teamColor" type="color" class="w-10 h-10 p-0.5 rounded" />
            <UInput v-model="form.teamColor" placeholder="#6366F1" size="sm" class="flex-1" />
          </div>
        </UFormField>

        <UFormField label="Icono del equipo">
          <UInput v-model="form.teamIcono" placeholder="i-lucide-users" size="sm" class="w-full" />
        </UFormField>
      </div>
    </div>

    <UFormField label="Nombre del espacio" :error="visibleErrors.nombre || false">
      <UInput v-model="form.nombre" placeholder="Ej: Diseño UI/UX" size="lg" class="w-full" @blur="touch('nombre')"
        @update:model-value="touch('nombre')" />
    </UFormField>

    <UFormField label="Descripción">
      <UTextarea v-model="form.descripcion" placeholder="Descripción del espacio..." :rows="3" class="w-full" />
    </UFormField>

    <UFormField label="Miembros">
      <MemberPicker v-model="miembros" />
    </UFormField>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Color">
        <div class="flex items-center gap-2">
          <UInput v-model="form.color" type="color" class="w-10 h-10 p-0.5 rounded" />
          <UInput v-model="form.color" placeholder="#8B5CF6" size="sm" class="flex-1" />
        </div>
      </UFormField>

      <UFormField label="Icono">
        <USelect v-model="form.icono" :items="[
          { label: 'Carpeta', value: 'i-lucide-folder', icon: 'i-lucide-folder' },
          { label: 'Tecnología', value: 'i-lucide-cpu', icon: 'i-lucide-cpu' },
          { label: 'Código', value: 'i-lucide-code', icon: 'i-lucide-code' },
          { label: 'Base de Datos', value: 'i-lucide-database', icon: 'i-lucide-database' },
          { label: 'Servidor', value: 'i-lucide-server', icon: 'i-lucide-server' },
          { label: 'Nube', value: 'i-lucide-cloud', icon: 'i-lucide-cloud' },
          { label: 'Aplicación', value: 'i-lucide-app-window', icon: 'i-lucide-app-window' },
          { label: 'Web', value: 'i-lucide-globe', icon: 'i-lucide-globe' },
          { label: 'Móvil', value: 'i-lucide-smartphone', icon: 'i-lucide-smartphone' },
          { label: 'API', value: 'i-lucide-waypoints', icon: 'i-lucide-waypoints' },
          { label: 'Panel', value: 'i-lucide-layout-dashboard', icon: 'i-lucide-layout-dashboard' },
          { label: 'Analítica', value: 'i-lucide-chart-column', icon: 'i-lucide-chart-column' },
          { label: 'Automatización', value: 'i-lucide-bot', icon: 'i-lucide-bot' },
          { label: 'IA', value: 'i-lucide-brain', icon: 'i-lucide-brain' },
          { label: 'Seguridad', value: 'i-lucide-shield', icon: 'i-lucide-shield' },
          { label: 'Configuración', value: 'i-lucide-settings', icon: 'i-lucide-settings' },
          { label: 'Documentación', value: 'i-lucide-file-text', icon: 'i-lucide-file-text' },
          { label: 'Tareas', value: 'i-lucide-list-todo', icon: 'i-lucide-list-todo' },
          { label: 'Equipo', value: 'i-lucide-users', icon: 'i-lucide-users' },
          { label: 'Maletín', value: 'i-lucide-briefcase', icon: 'i-lucide-briefcase' }
        ]" placeholder="Analítica" size="sm" class="w-full mt-1.25" />
      </UFormField>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <UButton label="Cancelar" size="sm" color="neutral" variant="ghost" class="px-10" @click="emit('cancelled')" />
      <UButton type="submit" label="Crear espacio" size="sm" color="primary" variant="solid" class="flex-1 flex justify-center"
        :loading="loading" :disabled="loading" />
    </div>
  </UForm>
</template>
