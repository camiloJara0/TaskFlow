<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const email = ref('')
const password = ref('')
const nombre = ref('')
const apellido = ref('')
const pais = ref('')
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  await new Promise(r => setTimeout(r, 1200))
  loading.value = false
  await navigateTo('/auth/login')
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
          Crea tu cuenta
        </h1>
        <p class="text-sm text-muted mt-1.5">
          Regístrate para comenzar
        </p>
      </div>

      <formsAuthRegisterForm @success="handleLogin"/>

      <p class="text-xs text-center text-muted mt-6">
        ¿Tienes cuenta?
        <ULink
          to="/auth/login"
          class="text-primary hover:underline font-medium"
        >Inicia Sesión</ULink>
      </p>
    </div>
  </div>
</template>
