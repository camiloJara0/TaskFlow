<script setup lang="ts">
import AuthResetPasswordForm from './AuthResetPasswordForm.vue';

const emit = defineEmits<{ success: [] }>()
const loading = ref(false)
const sent = ref(false)
const sendCode = ref(false)

const { form, visibleErrors, touch, submit: validate, setForm } = useFormValidation([
  { key: 'email', label: 'Email', required: true, isEmail: true }
])

setForm({ email: '' })

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  try {
    const auth = useAuthService()
    await auth.sendVerificationCode(form.value.email)
    sent.value = true
    sendCode.value = true
    emit('success')
  } finally {
    loading.value = false
  }
}

async function handleResetPassword() {
    loading.value = true
    await new Promise(r => setTimeout(r, 1200))
    loading.value = false
    sendCode.value = false
    await navigateTo('/auth/login')
}
</script>

<template>
  <UForm v-if="!sendCode" :state="form" @submit="handleSubmit" class="space-y-4">
    <template v-if="!sent">
      <p class="text-sm text-muted">
        Ingresa tu correo electrónico y te enviaremos un código para restablecer tu contraseña.
      </p>

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

      <UButton
        type="submit"
        label="Enviar código"
        size="lg"
        color="primary"
        variant="solid"
        class="w-full"
        :loading="loading"
        :disabled="loading"
      />
    </template>

    <template v-else>
      <UCard>
        <div class="text-center py-2">
          <UIcon name="i-lucide-mail-check" class="w-8 h-8 text-primary mx-auto mb-2" />
          <p class="text-sm font-medium">Código enviado</p>
          <p class="text-xs text-muted mt-1">
            Revisa tu correo. El código es válido por 15 minutos.
          </p>
        </div>
      </UCard>
    </template>
  </UForm>
  <AuthResetPasswordForm v-else @success="handleResetPassword" :email="form.value?.email"/>
</template>
