<script setup lang="ts">
const props = defineProps<{ email: string }>()
const emit = defineEmits<{ success: [] }>()
const loading = ref(false)
const show = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'codigo', label: 'Código', required: true, minLength: 6, maxLength: 6, pattern: /^\d{6}$/, patternMessage: 'El código debe ser de 6 dígitos' },
  { key: 'password', label: 'Nueva contraseña', required: true, minLength: 8 },
  { key: 'password_confirmation', label: 'Confirmar contraseña', required: true, match: 'password' }
])

setForm({ codigo: '', password: '', password_confirmation: '' })

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const auth = useAuthService()
      await auth.resetPassword({
        email: props.email,
        codigo: form.value.codigo,
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
  <UForm :state="form" @submit="handleSubmit" class="space-y-4">
    <p class="text-sm text-muted">
      Ingresa el código de 6 dígitos que enviamos a <strong>{{ email }}</strong> y tu nueva contraseña.
    </p>

    <UFormField label="Código de verificación" :error="visibleErrors.codigo || false">
      <UPinInput
        v-model="form.codigo"
        :length="6"
        size="lg"
        class="justify-center w-full mt-1"
        @update:model-value="touch('codigo')"
      />
    </UFormField>

    <UFormField label="Nueva contraseña" :error="visibleErrors.password || false">
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
      label="Restablecer contraseña"
      size="lg"
      color="primary"
      variant="solid"
      class="w-full flex justify-center"
      :loading="loading"
      :disabled="loading"
    />
  </UForm>
</template>
