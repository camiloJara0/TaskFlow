<script setup lang="ts">
const emit = defineEmits<{ success: [] }>()
const loading = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'email', label: 'Email', required: true, isEmail: true },
  { key: 'password', label: 'Contraseña', required: true, minLength: 8 }
])

setForm({ email: '', password: '' })

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const auth = useAuthService()
    await auth.login(form.value.email, form.value.password)
    emit('success')
    await navigateTo('/dashboard')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="space-y-4">
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

    <UFormField label="Contraseñala" :error="visibleErrors.password || false">
      <UInput
        v-model="form.password"
        type="password"
        placeholder="••••••••"
        size="lg"
        class="w-full"
        autocomplete="current-password"
        @blur="touch('password')"
        @update:model-value="touch('password')"
      />
    </UFormField>

    <div class="flex items-center justify-between">
      <UCheckbox label="Recordar sesión" />
      <NuxtLink to="/auth/forgot-password" class="text-xs text-primary hover:underline">
        ¿Olvidaste tu contraseña?
      </NuxtLink>
    </div>

    <UButton
      type="submit"
      label="Iniciar sesión"
      size="lg"
      color="primary"
      variant="solid"
      class="w-full flex justify-center"
      :loading="loading"
      :disabled="loading"
    />
  </UForm>
</template>
