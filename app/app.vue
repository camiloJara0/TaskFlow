<script setup lang="ts">
useAppearance()

useHead({
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1' }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'es'
  }
})

useSeoMeta({
  title: 'TaskFlow - Gestión de tareas',
  description: 'Plataforma de gestión de tareas, agendamiento y productividad'
})
</script>

<template>
  <UApp>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
