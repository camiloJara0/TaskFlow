<script setup lang="ts">
useAppearance()

useHead({
  meta: [
    { name: 'TaskFlow', content: 'width=device-width, initial-scale=1' }
  ],
  link: [
    { rel: 'icon', href: '/logo.ico' }
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
      <NuxtPwaManifest />
      <NuxtPwaAssets />
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
