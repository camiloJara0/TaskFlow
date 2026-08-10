<script setup lang="ts">
const { state, xpInfo, title, nextTitle, phase, todayCount, weekCount, monthCount, xpThisWeek, productivity } = useGamification()
</script>

<template>
  <div class="glass-card rounded-3xl overflow-hidden relative">
    <div class="absolute -top-24 -right-24 w-72 h-72 bg-[radial-gradient(circle,rgba(37,99,235,0.12),transparent_65%)] pointer-events-none" />
    <div class="absolute -bottom-32 -left-20 w-80 h-80 bg-[radial-gradient(circle,rgba(245,195,59,0.10),transparent_65%)] pointer-events-none" />

    <div class="p-6 sm:p-7">
      <div class="flex items-start justify-between flex-wrap gap-6">
        <!-- Mascota -->
        <div class="flex items-center gap-5">
          <div class="relative shrink-0">
            <GamificationPet :size="104" />
          </div>
          <div class="space-y-0.5">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-amber-500 dark:text-amber-400">
                {{ phase.name }}
              </span>
            </div>
            <div class="flex items-center gap-2.5">
              <span class="text-3xl font-heading font-extrabold tracking-tight text-highlighted">
                Nivel {{ state.level }}
              </span>
              <span
                class="text-[10px] font-bold px-2 py-1 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 ring-1 ring-blue-500/20"
              >
                {{ title.title }}
              </span>
            </div>
            <p class="text-xs text-muted">
              {{ phase.description }}
            </p>
          </div>
        </div>

        <!-- Racha -->
        <div class="flex items-center gap-3">
          <div
            class="px-4 py-2.5 rounded-2xl glass-chip text-center"
            :class="state.streak >= 30
              ? 'ring-1 ring-orange-400/40 shadow-lg shadow-orange-500/20'
              : state.streak >= 7
                ? 'ring-1 ring-amber-400/30'
                : ''"
          >
            <div class="text-2xl leading-none mb-0.5">
              <UIcon
                :name="state.streak >= 7 ? 'i-lucide-flame' : 'i-lucide-flame'"
                :class="state.streak >= 30 ? 'text-orange-500' : state.streak >= 7 ? 'text-amber-500' : 'text-slate-400'"
              />
            </div>
            <p class="text-sm font-heading font-bold text-highlighted">
              {{ state.streak }} días
            </p>
            <p class="text-[10px] text-muted">
              de racha
            </p>
          </div>
        </div>
      </div>

      <!-- Barra XP -->
      <div class="mt-6">
        <div class="flex items-center justify-between text-xs mb-2">
          <span class="font-medium text-muted">Experiencia</span>
          <span class="font-heading font-bold text-highlighted">
            {{ xpInfo.current }} / {{ xpInfo.needed }} XP
          </span>
        </div>
        <div class="relative h-3.5 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
          <div
            class="h-full rounded-full bg-linear-to-r from-blue-500 via-cyan-400 to-amber-400 shadow-[0_0_16px_rgba(59,130,246,0.5)] xp-fill transition-all duration-700 ease-out"
            :style="{ width: `${xpInfo.percent}%` }"
          />
          <div class="absolute inset-0 rounded-full overflow-hidden">
            <div class="xp-shimmer absolute inset-y-0 w-1/3 bg-white/25 blur-md" />
          </div>
        </div>
        <div class="flex items-center justify-between mt-1.5 text-[10px] text-muted">
          <span>{{ title.title }}</span>
          <span class="flex items-center gap-1">
            <UIcon
              :name="nextTitle.icon"
              class="w-3 h-3"
            />
            Siguiente: {{ nextTitle.title }}
          </span>
        </div>
      </div>

      <!-- Stats -->
      <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-xl bg-white/50 dark:bg-white/4 p-3">
          <p class="text-lg font-heading font-bold text-highlighted">
            {{ todayCount }}
          </p>
          <p class="text-[10px] text-muted">
            Hoy
          </p>
        </div>
        <div class="rounded-xl bg-white/50 dark:bg-white/4 p-3">
          <p class="text-lg font-heading font-bold text-highlighted">
            {{ weekCount }}
          </p>
          <p class="text-[10px] text-muted">
            Esta semana
          </p>
        </div>
        <div class="rounded-xl bg-white/50 dark:bg-white/4 p-3">
          <p class="text-lg font-heading font-bold text-highlighted">
            {{ monthCount }}
          </p>
          <p class="text-[10px] text-muted">
            Este mes
          </p>
        </div>
        <div class="rounded-xl bg-white/50 dark:bg-white/4 p-3">
          <p class="text-lg font-heading font-bold text-highlighted">
            {{ xpThisWeek }}
          </p>
          <p class="text-[10px] text-muted">
            XP semanal
          </p>
        </div>
      </div>

      <!-- Productividad + logros recientes -->
      <div class="mt-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-2.5">
          <div
            class="w-2 h-8 rounded-full bg-linear-to-t"
            :class="productivity.color"
          />
          <div>
            <p class="text-sm font-semibold text-highlighted">
              Productividad {{ productivity.label }}
            </p>
            <p class="text-[10px] text-muted">
              Puntuación {{ productivity.score }}
            </p>
          </div>
        </div>
        <NuxtLink
          to="/progress"
          class="flex items-center gap-2 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"
        >
          Ver progreso completo
          <UIcon
            name="i-lucide-arrow-right"
            class="w-3.5 h-3.5"
          />
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.xp-fill {
  background-image: linear-gradient(90deg, #2563eb, #06b6d4, #f5c33b);
}

.xp-shimmer {
  animation: xp-shimmer 2.4s ease-in-out infinite;
}

@keyframes xp-shimmer {
  0% { transform: translateX(-100%); }
  60%, 100% { transform: translateX(400%); }
}
</style>
