<script setup lang="ts">
import { RARITY_META, ACHIEVEMENT_CATEGORIES, PET_PHASES, STREAK_MILESTONES, streakLabel } from '~/utils/gamification'

const {
  state, xpInfo, title, nextTitle, phase,
  todayCount, weekCount, monthCount, xpThisWeek,
  achievementsUnlocked, lockedAchievements, productivity,
  soundEnabled, soundVolume, setSoundEnabled, setSoundVolume, play,
  reset
} = useGamification()

const activeCategory = ref<string>('all')

const filteredUnlocked = computed(() => {
  if (activeCategory.value === 'all') return achievementsUnlocked.value
  return achievementsUnlocked.value.filter((a) => {
    return a.category === activeCategory.value
  })
})

const filteredLocked = computed(() => {
  if (activeCategory.value === 'all') return lockedAchievements.value
  return lockedAchievements.value.filter((a) => {
    return a.category === activeCategory.value
  })
})

const unlockedByRarity = computed(() => {
  const counts: Record<string, number> = { comun: 0, raro: 0, epico: 0, legendario: 0 }
  achievementsUnlocked.value.forEach((a) => {
    counts[a.rarity] = (counts[a.rarity] || 0) + 1
  })
  return counts
})

const totalByRarity = computed(() => {
  const counts: Record<string, number> = { comun: 0, raro: 0, epico: 0, legendario: 0 }
  ACHIEVEMENTS.forEach((a) => {
    counts[a.rarity] = (counts[a.rarity] || 0) + 1
  })
  return counts
})

const nextMilestone = computed(() => STREAK_MILESTONES.find(m => m > state.value.streak) || null)

function formatUnlockDate(iso: string): string {
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })
}

function playPreview() {
  play('task')
}
</script>

<template>
  <div class="p-6 sm:p-8 max-w-5xl mx-auto space-y-8 page-enter">
    <div class="flex items-end justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-[0.14em]">Gamificación</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Progreso de tu leyenda
        </h1>
        <p class="text-sm text-muted mt-1">
          Cada tarea te acerca a la inmortalidad.
        </p>
      </div>
      <UButton
        label="Reiniciar progreso"
        icon="i-lucide-rotate-ccw"
        size="xs"
        color="neutral"
        variant="ghost"
        class="rounded-lg"
        @click="reset()"
      />
    </div>

    <!-- Héroes: mascota + nivel + XP -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 relative overflow-hidden">
      <div class="absolute -top-20 -right-20 w-64 h-64 bg-[radial-gradient(circle,rgba(245,195,59,0.12),transparent_65%)] pointer-events-none" />
      <div class="grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 items-center">
        <div class="flex justify-center">
          <GamificationPet :size="150" />
        </div>
        <div class="space-y-5">
          <div>
            <div class="flex items-center gap-3 flex-wrap">
              <span class="text-4xl font-heading font-extrabold tracking-tight text-highlighted">
                Nivel {{ state.level }}
              </span>
              <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/25">
                {{ title.title }}
              </span>
            </div>
            <p class="text-sm text-muted mt-1">
              {{ phase.name }} — {{ phase.description }}
            </p>
          </div>

          <div>
            <div class="flex items-center justify-between text-xs mb-2">
              <span class="font-medium text-muted">Experiencia</span>
              <span class="font-heading font-bold text-highlighted">{{ xpInfo.current }} / {{ xpInfo.needed }} XP</span>
            </div>
            <div class="h-4 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-linear-to-r from-blue-500 via-cyan-400 to-amber-400 shadow-[0_0_16px_rgba(59,130,246,0.5)] transition-all duration-700"
                :style="{ width: `${xpInfo.percent}%` }"
              />
            </div>
            <div class="flex justify-between mt-1.5 text-[11px] text-muted">
              <span>{{ title.title }}</span>
              <span>Siguiente: {{ nextTitle.title }}</span>
            </div>
          </div>

          <!-- Evoluciones de mascota -->
          <div class="space-y-1.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted">
              Evoluciones
            </p>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="p in PET_PHASES"
                :key="p.level"
                class="text-[10px] px-2 py-1 rounded-full"
                :class="state.level >= p.level
                  ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/30 font-semibold'
                  : 'bg-white/50 dark:bg-white/5 text-muted'"
              >
                {{ p.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Racha -->
    <div class="glass-card rounded-3xl p-6 sm:p-7 relative overflow-hidden">
      <div class="absolute -bottom-24 -left-16 w-64 h-64 bg-[radial-gradient(circle,rgba(245,158,11,0.10),transparent_65%)] pointer-events-none" />
      <div class="flex items-center justify-between flex-wrap gap-5">
        <div class="flex items-center gap-4">
          <div class="text-5xl">
            <UIcon
              name="i-lucide-flame"
              :class="state.streak >= 30 ? 'text-orange-500' : state.streak >= 7 ? 'text-amber-500' : 'text-slate-400'"
            />
          </div>
          <div>
            <p class="text-3xl font-heading font-extrabold text-highlighted">
              {{ state.streak }} días
            </p>
            <p class="text-sm text-muted">
              {{ streakLabel(state.streak) }}
            </p>
          </div>
        </div>
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <span
              v-for="m in STREAK_MILESTONES"
              :key="m"
              class="text-[11px] px-2 py-1 rounded-full"
              :class="state.streak >= m ? 'bg-orange-500/15 text-orange-600 dark:text-orange-400 ring-1 ring-orange-500/30 font-semibold' : 'bg-white/50 dark:bg-white/5 text-muted'"
            >
              🔥 {{ m }}
            </span>
          </div>
          <p
            v-if="nextMilestone"
            class="text-[11px] text-muted"
          >
            Siguiente hito: <span class="font-semibold text-orange-500 dark:text-orange-400">{{ nextMilestone }} días</span>
          </p>
          <p
            v-else
            class="text-[11px] text-muted font-semibold"
          >
            Has superado todos los hitos. Leyenda eterna.
          </p>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <!-- <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div class="glass-card rounded-2xl p-4">
        <p class="text-xl font-heading font-bold text-highlighted">
          {{ todayCount }}
        </p>
        <p class="text-[11px] text-muted">
          Hoy
        </p>
      </div>
      <div class="glass-card rounded-2xl p-4">
        <p class="text-xl font-heading font-bold text-highlighted">
          {{ weekCount }}
        </p>
        <p class="text-[11px] text-muted">
          Esta semana
        </p>
      </div>
      <div class="glass-card rounded-2xl p-4">
        <p class="text-xl font-heading font-bold text-highlighted">
          {{ monthCount }}
        </p>
        <p class="text-[11px] text-muted">
          Este mes
        </p>
      </div>
      <div class="glass-card rounded-2xl p-4">
        <p class="text-xl font-heading font-bold text-highlighted">
          {{ xpThisWeek }}
        </p>
        <p class="text-[11px] text-muted">
          XP semanal
        </p>
      </div>
      <div class="glass-card rounded-2xl p-4">
        <div class="flex items-center gap-2">
          <div
            class="w-1.5 h-8 rounded-full bg-linear-to-t"
            :class="productivity.color"
          />
          <div>
            <p class="text-sm font-heading font-bold text-highlighted">
              {{ productivity.label }}
            </p>
            <p class="text-[11px] text-muted">
              Puntuación {{ productivity.score }}
            </p>
          </div>
        </div>
      </div>
    </div> -->

    <!-- Logros -->
    <div class="space-y-5">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h2 class="text-lg font-heading font-bold text-highlighted">
            Logros
          </h2>
          <p class="text-sm text-muted">
            {{ achievementsUnlocked.length }} de {{ ACHIEVEMENTS.length }} desbloqueados
          </p>
        </div>
        <div class="flex items-center gap-1 p-1 glass-chip rounded-xl">
          <UButton
            v-for="c in [{ id: 'all', label: 'Todos', icon: 'i-lucide-layout-grid' }, ...ACHIEVEMENT_CATEGORIES]"
            :key="c.id"
            :icon="c.icon"
            :label="c.label"
            size="xs"
            color="neutral"
            :variant="activeCategory === c.id ? 'solid' : 'ghost'"
            class="rounded-lg"
            @click="activeCategory = c.id"
          />
        </div>
      </div>

      <!-- Rarezas -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div
          v-for="r in (['comun', 'raro', 'epico', 'legendario'] as const)"
          :key="r"
          class="glass-card rounded-xl p-3"
        >
          <div class="flex items-center justify-between">
            <span
              class="text-[10px] font-bold uppercase tracking-wider"
              :class="RARITY_META[r].text"
            >
              {{ RARITY_META[r].label }}
            </span>
            <span class="text-xs font-heading font-bold text-highlighted">
              {{ unlockedByRarity[r] }}/{{ totalByRarity[r] }}
            </span>
          </div>
          <div class="mt-2 h-1.5 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full"
              :class="RARITY_META[r].bg"
              :style="{ width: `${((unlockedByRarity[r] || 0) / Math.max(1, totalByRarity[r] || 0)) * 100}%` }"
            />
          </div>
        </div>
      </div>

      <!-- Desbloqueados -->
      <div
        v-if="filteredUnlocked.length > 0"
        class="space-y-2"
      >
        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted px-1">
          Desbloqueados
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="a in filteredUnlocked"
            :key="a.id"
            class="glass-card rounded-2xl p-4 flex items-center gap-3.5 animate-fade-up"
          >
            <div
              class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center shadow-md ring-1"
              :class="`${RARITY_META[a.rarity].bg} ${RARITY_META[a.rarity].ring}`"
            >
              <UIcon
                :name="a.icon"
                class="w-5 h-5"
                :class="RARITY_META[a.rarity].text"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-highlighted truncate">
                  {{ a.title }}
                </p>
              </div>
              <p class="text-xs text-muted truncate">
                {{ a.description }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <span
                class="text-[10px] font-medium px-2 py-0.5 rounded-full"
                :class="RARITY_META[a.rarity].bg + ' ' + RARITY_META[a.rarity].text"
              >
                {{ RARITY_META[a.rarity].label }}
              </span>
              <p class="text-[10px] text-muted mt-1">
                {{ formatUnlockDate(state.achievements[a.id] || '') }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bloqueados -->
      <div
        v-if="filteredLocked.length > 0"
        class="space-y-2"
      >
        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-muted px-1">
          Por descubrir
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="a in filteredLocked"
            :key="a.id"
            class="rounded-2xl p-4 flex items-center gap-3.5 bg-white/30 dark:bg-white/3 border border-dashed border-white/60 dark:border-white/10 opacity-70"
          >
            <div class="w-11 h-11 shrink-0 rounded-xl bg-white/40 dark:bg-white/5 flex items-center justify-center border border-white/60 dark:border-white/10">
              <UIcon
                name="i-lucide-lock"
                class="w-5 h-5 text-muted"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-muted truncate">
                {{ a.title }}
              </p>
              <p class="text-xs text-muted/70 truncate">
                {{ a.hint }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sonidos -->
    <div class="glass-card rounded-3xl p-6 sm:p-7">
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h2 class="text-lg font-heading font-bold text-highlighted">
            Sonidos de recompensa
          </h2>
          <p class="text-sm text-muted">
            Tonos épicos y discretos al completar tareas.
          </p>
        </div>
        <div class="flex items-center gap-4">
          <UButton
            icon="i-lucide-volume-2"
            size="sm"
            color="neutral"
            variant="ghost"
            class="rounded-xl"
            @click="playPreview"
          />
          <UInput
            v-model="soundVolume"
            type="range"
            min="0"
            max="1"
            step="0.05"
            class="w-32"
            @change="setSoundVolume(Number(soundVolume))"
          />
          <USwitch
            :model-value="soundEnabled"
            @update:model-value="setSoundEnabled"
          />
        </div>
      </div>
    </div>
  </div>
</template>
