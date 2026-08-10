<script setup lang="ts">
import AuthLoginForm from '~/components/forms/AuthLoginForm.vue'

definePageMeta({ layout: 'auth' })

const email = ref('')
const password = ref('')
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  await new Promise(r => setTimeout(r, 1200))
  loading.value = false
  await navigateTo('/dashboard')
}
</script>

<template>
  <div class="w-full max-w-md animate-scale-in">
    <div class="glass-panel rounded-3xl p-8 sm:p-10">
      <div class="text-center mb-8">
        <div class="relative inline-block mb-5">
          <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-xl shadow-blue-500/30">
            <span class="text-white font-extrabold text-xl">T</span>
          </div>
          <div class="absolute -inset-1.5 rounded-3xl bg-blue-500/25 blur-xl -z-10" />
        </div>
        <h1 class="text-2xl font-heading font-bold tracking-tight">
          Bienvenido de nuevo
        </h1>
        <p class="text-sm text-muted mt-1.5">
          Inicia sesión para continuar
        </p>
      </div>

      <AuthLoginForm />
      <!-- <UForm :state="{ email, password }" class="space-y-4" @submit="handleLogin">
      <UFormField label="Correo electrónico">
        <UInput
          v-model="email"
          type="email"
          placeholder="tu@correo.com"
          size="lg"
          class="w-full"
          autocomplete="email"
        />
      </UFormField>

      <UFormField label="Contraseña">
        <UInput
          v-model="password"
          type="password"
          placeholder="••••••••"
          size="lg"
          class="w-full"
          autocomplete="current-password"
        />
      </UFormField>

      <div class="flex items-center justify-between">
        <UCheckbox label="Recordar sesión" />
        <ULink to="#" class="text-xs text-primary hover:underline">¿Olvidaste tu contraseña?</ULink>
      </div>

      <UButton
        type="submit"
        :label="loading ? 'Iniciando sesión...' : 'Iniciar sesión'"
        size="lg"
        color="primary"
        variant="solid"
        class="w-full"
        :loading="loading"
        :disabled="loading"
      />

    </UForm> -->
      <p class="text-xs text-center text-muted mt-6">
        ¿No tienes cuenta?
        <ULink
          to="/auth/register"
          class="text-primary hover:underline font-medium"
        >Regístrate</ULink>
      </p>
    </div>
  </div>
</template>
