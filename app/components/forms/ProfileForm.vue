<script setup lang="ts">
const props = defineProps<{
  user?: Record<string, any>
}>()

const emit = defineEmits<{ saved: [data: any] }>()
const loading = ref(false)
const user = ref<Record<string, any>>({})

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'nombre', label: 'Nombre', required: true, maxLength: 255 }
])
const foto = ref<File | null>(null)
const url = ref('https://api.dicebear.com/9.x/initials/svg?seed=CM&backgroundColor=2563eb&textColor=ffffff')

onMounted(() => {
  const userLocal = localStorage.getItem('user')
  if (userLocal) {
    user.value = JSON.parse(userLocal)
  }
  url.value = user.value?.foto
  setForm({
    nombre: user.value?.nombre || '',
    email: user.value?.email || '',
    foto: user.value?.foto || null,
    zona_horaria: user.value?.zona_horaria || 'America/Mexico_City',
    idioma: user.value?.idioma || 'es',
    tema: user.value?.tema || 'claro'
  })
})

const zonasHorarias = [
  { label: 'Ciudad de México (GMT-6)', value: 'America/Mexico_City' },
  { label: 'Bogotá (GMT-5)', value: 'America/Bogota' },
  { label: 'Buenos Aires (GMT-3)', value: 'America/Argentina/Buenos_Aires' },
  { label: 'Madrid (GMT+2)', value: 'Europe/Madrid' },
  { label: 'Santiago (GMT-4)', value: 'America/Santiago' },
  { label: 'UTC', value: 'UTC' }
]

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const profile = useProfileService()
    const res = await profile.updateProfile(form.value, foto.value)
    emit('saved', res.data)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="glass-card rounded-2xl p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
    <UFormField label="Nombre" :error="visibleErrors.nombre || false">
      <UInput
        v-model="form.nombre"
        placeholder="Tu nombre"
        size="lg"
        class="w-full"
        @blur="touch('nombre')"
        @update:model-value="touch('nombre')"
      />
    </UFormField>

    <UFormField
      label="Correo electrónico"
    >
      <UInput
        v-model="form.email"
        placeholder="Tu correo"
        size="sm"
        type="email"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Zona horaria">
      <USelect
        v-model="form.zona_horaria"
        :items="zonasHorarias"
        class="w-full"
        size="sm"
      />
    </UFormField>

    <UFormField label="Idioma">
      <USelect
        v-model="form.idioma"
        :items="[
          { label: 'Español', value: 'es' },
          { label: 'English', value: 'en' }
        ]"
        class="w-full"
        size="sm"
      />
    </UFormField>

    <UFormField label="Tema" class="sm:col-span-2">
      <div class="flex gap-2">
        <UButton
          label="Claro"
          icon="i-lucide-sun"
          size="sm"
          :color="form.tema === 'claro' ? 'primary' : 'neutral'"
          :variant="form.tema === 'claro' ? 'solid' : 'outline'"
          class="flex-1"
          @click="form.tema = 'claro'"
        />
        <UButton
          label="Oscuro"
          icon="i-lucide-moon"
          size="sm"
          :color="form.tema === 'oscuro' ? 'primary' : 'neutral'"
          :variant="form.tema === 'oscuro' ? 'solid' : 'outline'"
          class="flex-1"
          @click="form.tema = 'oscuro'"
        />
      </div>
    </UFormField>

    <div class="glass-card rounded-2xl p-6 sm:col-span-2">
      <div class="flex items-center justify-center gap-5">
        <div class="relative shrink-0">
          <UAvatar
            :src="form?.foto || `https://api.dicebear.com/9.x/initials/svg?seed=${form?.nombre?.charAt() || 'UN'}&backgroundColor=2563eb&textColor=ffffff`"
            size="xl"
            class="rounded-2xl shadow-lg shadow-blue-500/20"
          />
          <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-green-500 ring-2 ring-white dark:ring-bg" />
        </div>
        <div>
          <UFileUpload
            v-model="foto"
            label="PNG o JPG. Máx 2MB."
            size="sm"
            class="flex-1"
            @keydown.enter.prevent="handleSubmit"
          />
        </div>

      </div>
    </div>

    <UButton
      type="submit"
      label="Guardar cambios"
      size="sm"
      color="primary"
      variant="solid"
      class="sm:col-span-2 flex justify-center"
      :loading="loading"
      :disabled="loading"
    />
  </UForm>
</template>
