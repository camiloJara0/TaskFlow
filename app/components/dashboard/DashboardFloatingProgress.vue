<script setup lang="ts">
import { petPhaseFor } from '~/utils/gamification'

const { state, xpInfo, title, phase, todayCount } = useGamification()

const expanded = ref(false)
const currentPhase = computed(() => phase.value || petPhaseFor(state.value.level))
</script>

<template>
  <div class="fixed bottom-5 right-5 z-40">
    <div
      class="relative transition-all duration-300"
      :class="expanded ? 'w-72' : 'w-auto'"
    >
      <div
        v-if="expanded"
        class="glass-card rounded-2xl p-4 mb-3 shadow-xl shadow-blue-500/10"
      >
        <div class="flex items-center gap-3 mb-3">
          <GamificationPet
            :size="48"
            class="shrink-0"
          />
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500 dark:text-amber-400">
                {{ currentPhase.name }}
              </p>
              <span class="text-[9px] font-semibold text-muted">
                Nivel {{ state.level }}
              </span>
            </div>
            <div class="flex items-center gap-1.5 mt-0.5">
              <UIcon
                :name="title.icon"
                class="w-3 h-3 text-blue-500"
              />
              <p class="text-xs font-semibold text-highlighted truncate">
                {{ title.title }}
              </p>
            </div>
          </div>
          <UButton
            icon="i-lucide-chevron-down"
            size="xs"
            color="neutral"
            variant="ghost"
            class="shrink-0"
            aria-label="Contraer progreso"
            @click="expanded = false"
          />
        </div>

        <div>
          <div class="flex items-center justify-between text-[10px] mb-1">
            <span class="text-muted">Experiencia del nivel</span>
            <span class="font-semibold text-highlighted">
              {{ xpInfo.current }} / {{ xpInfo.needed }}
            </span>
          </div>
          <div class="h-2 bg-black/5 dark:bg-white/5 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full bg-linear-to-r from-blue-500 via-cyan-400 to-amber-400 transition-all duration-700"
              :style="{ width: `${xpInfo.percent}%` }"
            />
          </div>
        </div>

        <div class="flex items-center justify-between mt-3 pt-3 border-t border-default/70">
          <div class="flex items-center gap-1.5">
            <UIcon
              name="i-lucide-flame"
              class="w-4 h-4"
              :class="state.streak >= 30 ? 'text-orange-500' : state.streak >= 7 ? 'text-amber-500' : 'text-slate-400'"
            />
            <div class="text-xs">
              <span class="font-heading font-bold text-highlighted">{{ state.streak }} días</span>
              <span class="text-muted"> de racha</span>
            </div>
          </div>
          <div class="text-[10px] text-muted">
            {{ todayCount }} hoy
          </div>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-2.5 glass-card rounded-full pl-2 pr-4 py-2 shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition-all hover:scale-[1.02] cursor-pointer"
        :aria-label="expanded ? 'Contraer progreso' : 'Expandir progreso'"
        @click="expanded = !expanded"
      >
        <span
          class="relative flex items-center justify-center w-9 h-9 rounded-full"
          :class="state.streak >= 7 ? 'bg-linear-to-br from-orange-400 to-amber-500 shadow-orange-500/30' : 'bg-linear-to-br from-blue-500 to-cyan-400 shadow-blue-500/30'"
        >
          <UIcon
            :name="state.streak >= 7 ? 'i-lucide-flame' : 'i-lucide-sparkles'"
            class="w-4 h-4 text-white"
          />
          <span
            v-if="state.streak > 0"
            class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-white dark:bg-slate-800 text-[8px] font-bold text-amber-600 dark:text-amber-400 ring-1 ring-amber-300/50 flex items-center justify-center"
          >
            {{ state.streak }}
          </span>
        </span>
        <div class="text-left min-w-0">
          <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-muted leading-none">
            Nivel {{ state.level }}
          </p>
          <p class="text-xs font-heading font-bold text-highlighted truncate max-w-28">
            {{ title.title }}
          </p>
        </div>
        <UIcon
          :name="expanded ? 'i-lucide-chevron-down' : 'i-lucide-chevron-up'"
          class="w-4 h-4 text-muted"
        />
      </button>
    </div>
  </div>
</template>
