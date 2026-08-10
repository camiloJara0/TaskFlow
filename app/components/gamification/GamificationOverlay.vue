<script setup lang="ts">
import { RARITY_META, streakLabel } from '~/utils/gamification'

const {
  xpEvents, levelUpEvent, achievementQueue, streakEvent, confettiBurst,
  dismissLevelUp, dismissNextAchievement, dismissStreak
} = useGamification()

const { play } = useGameSound()

const levelOpen = computed({
  get: () => !!levelUpEvent.value,
  set: (v) => { if (!v) dismissLevelUp() }
})

const streakOpen = computed({
  get: () => !!streakEvent.value,
  set: (v) => { if (!v) dismissStreak() }
})

const currentAchievement = computed(() => achievementQueue.value[0] || null)

const achievementOpen = computed({
  get: () => !!currentAchievement.value,
  set: (v) => { if (!v) dismissNextAchievement() }
})

function playLevelSound() {
  play('level')
}
</script>

<template>
  <!-- Confeti -->
  <GamificationConfetti :burst="confettiBurst" />

  <!-- XP flotante -->
  <div class="pointer-events-none fixed bottom-24 right-6 z-95 flex flex-col items-end gap-2">
    <TransitionGroup enter-active-class="xp-enter-active" leave-active-class="xp-leave-active">
      <div v-for="e in xpEvents" :key="e.id"
        class="xp-float chip font-heading font-bold text-sm px-3.5 py-2 rounded-xl glass-panel shadow-lg shadow-amber-500/20 text-amber-600 dark:text-amber-400">
        <span class="flex items-center gap-1.5">
          <UIcon name="i-lucide-sparkles" class="w-4 h-4" />
          +{{ e.amount }} XP
        </span>
      </div>
    </TransitionGroup>
  </div>

  <!-- Subida de nivel -->
  <UModal v-model:open="levelOpen" :ui="{ content: 'glass-panel rounded-3xl overflow-hidden max-w-sm' }">
    <template #content>
      <div class="relative p-8 text-center">
        <div
          class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(245,195,59,0.18),transparent_65%)]" />
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-linear-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-xl shadow-amber-500/40 level-burst">
          <UIcon :name="levelUpEvent?.icon || 'i-lucide-star'" class="w-8 h-8 text-white" />
        </div>
        <span
          class="text-[10px] font-bold uppercase tracking-[0.18em] text-amber-500 dark:text-amber-400">Ascenso</span>
        <h3 class="text-2xl font-heading font-extrabold mt-1 text-highlighted">
          ¡Has alcanzado el Nivel {{ levelUpEvent?.level }}!
        </h3>
        <p class="text-sm text-muted mt-1.5">
          Ahora eres <span class="font-semibold text-amber-600 dark:text-amber-400">{{ levelUpEvent?.title }}</span>
        </p>
        <UButton label="¡Continuar mi leyenda!" size="sm" color="primary" variant="solid"
          class="mt-6 rounded-xl shadow-lg shadow-blue-500/25" @click="dismissLevelUp" />
      </div>
    </template>
  </UModal>

  <!-- Racha alcanzada -->
  <UModal v-model:open="streakOpen" :ui="{ content: 'glass-panel rounded-3xl overflow-hidden max-w-sm' }">
    <template #content>
      <div class="relative p-8 text-center">
        <div
          class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(245,158,11,0.18),transparent_65%)]" />
        <div class="text-6xl mb-3 flame-pop">
          🔥
        </div>
        <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-orange-500 dark:text-orange-400">Hito de
          racha</span>
        <h3 class="text-2xl font-heading font-extrabold mt-1 text-highlighted">
          ¡{{ streakEvent?.days }} días seguidos!
        </h3>
        <p class="text-sm text-muted mt-1.5">
          {{ streakEvent ? streakLabel(streakEvent.days) : '' }}. Tu constancia es legendaria.
        </p>
        <UButton label="¡Seguir encendido!" size="sm" color="primary" variant="solid"
          class="mt-6 rounded-xl shadow-lg shadow-orange-500/25" @click="dismissStreak" />
      </div>
    </template>
  </UModal>

  <!-- Logro desbloqueado -->
  <UModal v-model:open="achievementOpen" :ui="{ content: 'glass-panel rounded-3xl overflow-hidden max-w-sm' }">
    <template #content>
      <div v-if="currentAchievement" class="relative p-8 text-center">
        <div class="absolute inset-0 -z-10"
          :class="`bg-[radial-gradient(circle_at_50%_0%,rgba(245,195,59,0.14),transparent_65%)]`" />
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center shadow-xl ring-1 achievement-burst"
          :class="`${RARITY_META[currentAchievement.achievement.rarity].bg} ${RARITY_META[currentAchievement.achievement.rarity].ring} ${RARITY_META[currentAchievement.achievement.rarity].glow} shadow-amber-500/20`">
          <UIcon :name="currentAchievement.achievement.icon" class="w-8 h-8"
            :class="RARITY_META[currentAchievement.achievement.rarity].text" />
        </div>
        <span class="text-[10px] font-bold uppercase tracking-[0.18em]"
          :class="RARITY_META[currentAchievement.achievement.rarity].text">
          {{ RARITY_META[currentAchievement.achievement.rarity].label }}
        </span>
        <h3 class="text-2xl font-heading font-extrabold mt-1 text-highlighted">
          {{ currentAchievement.achievement.title }}
        </h3>
        <p class="text-sm text-muted mt-1.5">
          {{ currentAchievement.achievement.description }}
        </p>
        <UButton :label="achievementQueue.length > 1 ? `Siguiente (${achievementQueue.length - 1})` : '¡Increíble!'"
          size="sm" color="primary" variant="solid" class="mt-6 rounded-xl shadow-lg shadow-blue-500/25"
          @click="dismissNextAchievement" />
      </div>

    </template>
  </UModal>

  <!-- Reproducir sonido al abrir niveles (permitido tras interacción) -->
  <div class="hidden" @click="playLevelSound" />
</template>

<style scoped>
.xp-enter-active {
  transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.xp-leave-active {
  transition: all 0.4s ease-in;
}

.xp-enter-from,
.xp-leave-to {
  opacity: 0;
  transform: translateY(14px) scale(0.9);
}

.xp-float {
  animation: xp-float-up 2.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes xp-float-up {
  0% {
    opacity: 0;
    transform: translateY(18px) scale(0.85);
  }

  12% {
    opacity: 1;
    transform: translateY(0) scale(1.05);
  }

  20% {
    transform: translateY(0) scale(1);
  }

  70% {
    opacity: 1;
  }

  100% {
    opacity: 0;
    transform: translateY(-34px) scale(0.95);
  }
}

.level-burst {
  animation: level-burst 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes level-burst {
  0% {
    transform: scale(0.3) rotate(-12deg);
    opacity: 0;
  }

  60% {
    transform: scale(1.15) rotate(4deg);
    opacity: 1;
  }

  100% {
    transform: scale(1) rotate(0deg);
  }
}

.achievement-burst {
  animation: achievement-burst 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes achievement-burst {
  0% {
    transform: scale(0.4) rotate(20deg);
    opacity: 0;
  }

  60% {
    transform: scale(1.12) rotate(-6deg);
  }

  100% {
    transform: scale(1) rotate(0deg);
  }
}

.flame-pop {
  animation: flame-pop 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes flame-pop {
  0% {
    transform: scale(0.4);
    opacity: 0;
  }

  60% {
    transform: scale(1.25);
  }

  100% {
    transform: scale(1);
  }
}
</style>
