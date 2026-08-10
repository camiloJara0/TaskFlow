<script setup lang="ts">
const props = withDefaults(defineProps<{
  multiple?: boolean
}>(), {
  multiple: true
})

const model = defineModel<any>()

const { getProfiles } = useProfileService()
const profiles = ref<any[]>([])

const items = computed(() =>
  profiles.value.map((p: any) => ({
    label: p.email,
    description: p.nombre,
    value: p.id,
    avatar: p.foto ? { src: p.foto } : { text: memberInitials(p.nombre) }
  }))
)

function memberInitials(name: string) {
  if (model.value == null) {
  model.value = props.multiple ? [] : null
  }
  const parts = (name || '?').trim().split(/\s+/)
  if (parts.length > 1) return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase()
  return (name || '?').slice(0, 2).toUpperCase()
}

onMounted(async () => {
  const res = await getProfiles()
  profiles.value = res.data as unknown as any[]
})
</script>

<template>
  <UInputMenu
    v-model="model"
    :multiple="multiple"
    :items="items"
    value-key="value"
    :placeholder="multiple ? 'Seleccionar miembros' : 'Sin asignar'"
    icon="i-lucide-at-sign"
    class="w-full"
  />
</template>
