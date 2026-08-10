<script setup lang="ts">
const emit = defineEmits<{ success: [] }>()
const loading = ref(false)
const show = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'nombre', label: 'Nombre', required: true, maxLength: 255 },
  { key: 'email', label: 'Email', required: true, isEmail: true },
  { key: 'password', label: 'Contraseña', required: true, minLength: 8 },
  { key: 'password_confirmation', label: 'Confirmar contraseña', required: true, match: 'password' }
])

setForm({ nombre: '', email: '', password: '', password_confirmation: '' })

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
      const auth = useAuthService()
      await auth.register({
        nombre: form.value.nombre,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
      })
    emit('success')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="flex flex-col gap-3">
    <UFormField label="Nombre completo" :error="visibleErrors.nombre || false">
      <UInput
        v-model="form.nombre"
        placeholder="Tu nombre"
        size="lg"
        class="w-full"
        autocomplete="name"
        @blur="touch('nombre')"
        @update:model-value="touch('nombre')"
      />
    </UFormField>

    <UFormField label="Correo electrónico" :error="visibleErrors.email || false">
      <UInput
        v-model="form.email"
        type="email"
        placeholder="tu@correo.com"
        size="lg"
        class="w-full"
        autocomplete="email"
        @blur="touch('email')"
        @update:model-value="touch('email')"
      />
    </UFormField>

    <UFormField label="Contraseña" :error="visibleErrors.password || false">
      <UInput
        v-model="form.password"
        :type="show ? 'text' : 'password'"
        placeholder="Mínimo 8 caracteres"
        size="lg"
        class="w-full"
        autocomplete="new-password"
        @blur="touch('password')"
        @update:model-value="touch('password')"
      >
        <template #trailing>
      <UButton
        color="neutral"
        variant="link"
        size="sm"
        :icon="show ? 'i-lucide-eye-off' : 'i-lucide-eye'"
        :aria-label="show ? 'Hide password' : 'Show password'"
        :aria-pressed="show"
        aria-controls="password"
        @click="show = !show"
      />
    </template>
    </UInput>
      <template v-if="form.password.length > 0" #hint>
        <div class="flex gap-1 mt-1">
          <div
            class="h-1 flex-1 rounded-full transition-colors"
            :class="form.password.length >= 8 ? 'bg-green-500' : 'bg-border'"
          />
          <div
            class="h-1 flex-1 rounded-full transition-colors"
            :class="form.password.length >= 12 ? 'bg-green-500' : 'bg-border'"
          />
          <div
            class="h-1 flex-1 rounded-full transition-colors"
            :class="form.password.length >= 16 ? 'bg-green-500' : 'bg-border'"
          />
        </div>
      </template>
    </UFormField>

    <UFormField label="Confirmar contraseña" :error="visibleErrors.password_confirmation || false">
      <UInput
        v-model="form.password_confirmation"
        type="password"
        placeholder="Repite la contraseña"
        size="lg"
        class="w-full"
        autocomplete="new-password"
        @blur="touch('password_confirmation')"
        @update:model-value="touch('password_confirmation')"
      />
    </UFormField>

    <UButton
      type="submit"
      label="Crear cuenta"
      size="lg"
      color="primary"
      variant="solid"
      class="w-full flex justify-center"
      :loading="loading"
      :disabled="loading"
    />
  </UForm>
</template>
