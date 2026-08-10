<template>
  <div class="relative min-h-screen flex items-center justify-center p-4 overflow-hidden">
    <div
      class="absolute -top-32 -left-24 w-[28rem] h-[28rem] rounded-full bg-blue-500/25 blur-3xl animate-float-slow pointer-events-none"
    />
    <div
      class="absolute -bottom-40 -right-24 w-[30rem] h-[30rem] rounded-full bg-cyan-400/20 blur-3xl animate-float-slow pointer-events-none"
      style="animation-delay: -3s"
    />
    <div
      class="absolute top-1/3 -left-32 w-80 h-80 rounded-full bg-indigo-400/20 blur-3xl pointer-events-none"
    />
    <div class="relative w-full flex items-center justify-center">
      <NuxtPage />
    </div>
  </div>
</template>
