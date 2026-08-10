<script setup lang="ts">
import MemberPicker from './MemberPicker.vue';

const props = defineProps<{
  team?: Record<string, any>
}>()

const emit = defineEmits<{ saved: [data: any]; cancelled: [] }>()
const loading = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'nombre', label: 'Nombre', required: true, maxLength: 255 }
])

onMounted(() => {
  setForm({
    nombre: props.team?.nombre || '',
    descripcion: props.team?.descripcion || '',
    icono: props.team?.icono || '',
    color: props.team?.color || '#6366F1',
    miembros: props.team?.miembros?.map((m: any) => m.usuario?.id ?? m.usuario_id) || []
  })
})

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const teams = useTeamsService()
    let teamId = props.team?.id
    if (props.team?.id) {
      await teams.update(props.team.id, form.value)
    } else {
      const res = await teams.create({
        nombre: form.value.nombre,
        descripcion: form.value.descripcion || undefined,
        icono: form.value.icono || undefined,
        color: form.value.color || undefined,
      })
      teamId = res.data?.id
    }
    if (teamId) {
      await teams.syncMembers(teamId, form.value.miembros || [])
    }
    emit('saved', form.value)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="space-y-4">
    <UFormField label="Nombre del equipo" :error="visibleErrors.nombre || false">
      <UInput v-model="form.nombre" placeholder="Ej: Equipo de diseño" size="lg" class="w-full" @blur="touch('nombre')"
        @update:model-value="touch('nombre')" />
    </UFormField>

    <UFormField label="Descripción">
      <UTextarea v-model="form.descripcion" placeholder="¿Para qué es este equipo?" :rows="3" class="w-full" />
    </UFormField>

    <UFormField label="Miembros">
      <MemberPicker v-model="form.miembros" />
    </UFormField>

    <div class="grid grid-cols-2 gap-3">
      <UFormField label="Color">
        <div class="flex items-center gap-2">
          <UInput v-model="form.color" type="color" class="w-10 h-10 p-0.5 rounded" />
          <UInput v-model="form.color" placeholder="#6366F1" size="sm" class="flex-1" />
        </div>
      </UFormField>

      <UFormField label="Icono">
        <UInput v-model="form.icono" placeholder="i-lucide-users" size="sm" class="w-full" />
      </UFormField>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <UButton label="Cancelar" size="sm" color="neutral" variant="ghost" class="px-10" @click="emit('cancelled')" />
      <UButton type="submit" :label="team?.id ? 'Guardar cambios' : 'Crear equipo'" size="sm" color="primary"
        variant="solid" class="flex-1 flex justify-center" :loading="loading" :disabled="loading" />
    </div>
  </UForm>
</template>
