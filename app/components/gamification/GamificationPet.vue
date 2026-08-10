<script setup lang="ts">
import type { PetPhase } from '~/utils/gamification'
import { PET_PHASES, petPhaseFor } from '~/utils/gamification'

const props = defineProps<{
  phase?: PetPhase
  reaction?: 'idle' | 'happy' | 'thrilled'
  pulse?: number
  size?: number
}>()

const { state, petReaction } = useGamification()

const currentPhase = computed<PetPhase>(() => props.phase || petPhaseFor(state.value.level))
const currentReaction = computed(() => props.reaction || petReaction.value)
const displaySize = computed(() => props.size || 120)

const phaseIndex = computed(() => {
  const idx = PET_PHASES.findIndex(p => p.level === currentPhase.value.level)
  return idx === -1 ? 0 : idx
})

function reactionClass(): string {
  if (currentReaction.value === 'thrilled') return 'pet-thrilled'
  if (currentReaction.value === 'happy') return 'pet-happy'
  return 'pet-idle'
}
</script>

<template>
  <div
    class="pet-stage relative select-none"
    :class="reactionClass()"
    :style="{ width: `${displaySize}px`, height: `${displaySize}px` }"
  >
    <!-- Aura (fase 5+) -->
    <div
      v-if="phaseIndex >= 4"
      class="pet-aura absolute inset-0 rounded-full"
      :class="currentReaction === 'thrilled' ? 'aura-strong' : ''"
    />

    <!-- Anillos orbitales (fase 6+) -->
    <div
      v-if="phaseIndex >= 5"
      class="absolute inset-[-12px] rounded-full border border-amber-300/40 dark:border-amber-400/30 pet-orbit"
    >
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-amber-300 dark:bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.8)]" />
    </div>

    <!-- Mascota -->
    <div class="absolute inset-0 pet-body">
      <svg
        :viewBox="'0 0 120 120'"
        class="w-full h-full pet-bob"
        :class="currentReaction === 'thrilled' ? 'bob-fast' : ''"
      >
        <defs>
          <linearGradient
            id="pet-body-grad"
            x1="0"
            y1="0"
            x2="0"
            y2="1"
          >
            <stop
              offset="0%"
              stop-color="#EAF2FF"
            />
            <stop
              offset="55%"
              stop-color="#C7DBFF"
            />
            <stop
              offset="100%"
              stop-color="#9DBEFF"
            />
          </linearGradient>
          <linearGradient
            id="pet-belly-grad"
            x1="0"
            y1="0"
            x2="0"
            y2="1"
          >
            <stop
              offset="0%"
              stop-color="#FFFFFF"
            />
            <stop
              offset="100%"
              stop-color="#E4EEFF"
            />
          </linearGradient>
          <linearGradient
            id="pet-gold-grad"
            x1="0"
            y1="0"
            x2="1"
            y2="1"
          >
            <stop
              offset="0%"
              stop-color="#FDE68A"
            />
            <stop
              offset="50%"
              stop-color="#F5C33B"
            />
            <stop
              offset="100%"
              stop-color="#D99A1F"
            />
          </linearGradient>
          <linearGradient
            id="pet-wing-grad"
            x1="0"
            y1="0"
            x2="1"
            y2="1"
          >
            <stop
              offset="0%"
              stop-color="#D6E4FF"
            />
            <stop
              offset="100%"
              stop-color="#A8C6FF"
            />
          </linearGradient>
          <radialGradient
            id="pet-eye"
            cx="0.5"
            cy="0.4"
            r="0.6"
          >
            <stop
              offset="0%"
              stop-color="#4A6BF0"
            />
            <stop
              offset="100%"
              stop-color="#233B8F"
            />
          </radialGradient>
          <radialGradient
            id="pet-aura-grad"
            cx="0.5"
            cy="0.5"
            r="0.5"
          >
            <stop
              offset="0%"
              stop-color="rgba(251,191,36,0.35)"
            />
            <stop
              offset="60%"
              stop-color="rgba(251,191,36,0.12)"
            />
            <stop
              offset="100%"
              stop-color="rgba(251,191,36,0)"
            />
          </radialGradient>
        </defs>

        <!-- halo de partículas (fase 7) -->
        <g
          v-if="phaseIndex >= 6"
          class="pet-sparkles"
        >
          <circle
            cx="24"
            cy="30"
            r="2"
            fill="#FDE68A"
            class="sparkle-a"
          />
          <circle
            cx="96"
            cy="26"
            r="2.5"
            fill="#F5C33B"
            class="sparkle-b"
          />
          <circle
            cx="20"
            cy="70"
            r="1.5"
            fill="#93B4FD"
            class="sparkle-c"
          />
          <circle
            cx="102"
            cy="74"
            r="2"
            fill="#FDE68A"
            class="sparkle-a"
          />
          <circle
            cx="60"
            cy="14"
            r="2"
            fill="#F5C33B"
            class="sparkle-b"
          />
        </g>

        <!-- alas (fase 3+) -->
        <g
          v-if="phaseIndex >= 2"
          class="pet-wings"
        >
          <path
            d="M42 52 C 28 34, 14 38, 12 48 C 18 50, 24 56, 28 62 C 34 60, 38 58, 42 52Z"
            fill="url(#pet-wing-grad)"
            stroke="#8FADF5"
            stroke-width="1"
            opacity="0.95"
          />
          <path
            d="M78 52 C 92 34, 106 38, 108 48 C 102 50, 96 56, 92 62 C 86 60, 82 58, 78 52Z"
            fill="url(#pet-wing-grad)"
            stroke="#8FADF5"
            stroke-width="1"
            opacity="0.95"
          />
        </g>

        <!-- cuerpo -->
        <ellipse
          cx="60"
          cy="66"
          rx="34"
          ry="30"
          fill="url(#pet-body-grad)"
          stroke="rgba(90,120,200,0.35)"
          stroke-width="1.5"
        />
        <ellipse
          cx="60"
          cy="74"
          rx="20"
          ry="14"
          fill="url(#pet-belly-grad)"
        />

        <!-- detalles dorados (fase 2+) -->
        <g v-if="phaseIndex >= 1">
          <path
            d="M42 58 Q60 68 78 58"
            fill="none"
            stroke="url(#pet-gold-grad)"
            stroke-width="2.5"
            stroke-linecap="round"
          />
          <circle
            cx="60"
            cy="63"
            r="2.5"
            fill="#F5C33B"
          />
        </g>

        <!-- armadura divina (fase 4+) -->
        <g v-if="phaseIndex >= 3">
          <path
            d="M48 70 L52 62 L68 62 L72 70 L60 78 Z"
            fill="url(#pet-gold-grad)"
            stroke="#C9922A"
            stroke-width="1"
          />
          <circle
            cx="60"
            cy="69"
            r="3"
            fill="#4A6BF0"
          />
        </g>

        <!-- rostro -->
        <g :class="currentReaction === 'idle' ? 'eyes-idle' : 'eyes-happy'">
          <circle
            cx="49"
            cy="58"
            r="4"
            fill="url(#pet-eye)"
          />
          <circle
            cx="71"
            cy="58"
            r="4"
            fill="url(#pet-eye)"
          />
          <circle
            cx="50.5"
            cy="56.5"
            r="1.4"
            fill="#FFFFFF"
          />
          <circle
            cx="72.5"
            cy="56.5"
            r="1.4"
            fill="#FFFFFF"
          />
        </g>

        <!-- sonrisa / mejillas -->
        <path
          v-if="currentReaction !== 'idle'"
          d="M55 68 Q60 72.5 65 68"
          fill="none"
          stroke="#3E5FBF"
          stroke-width="1.8"
          stroke-linecap="round"
        />
        <circle
          cx="43"
          cy="64"
          r="3"
          fill="#F9A8C9"
          opacity="0.45"
        />
        <circle
          cx="77"
          cy="64"
          r="3"
          fill="#F9A8C9"
          opacity="0.45"
        />

        <!-- antena dorada -->
        <path
          d="M60 36 Q60 26 64 22"
          fill="none"
          stroke="url(#pet-gold-grad)"
          stroke-width="2"
          stroke-linecap="round"
        />
        <path
          d="M64 22 l4 2 l-4 2 Z"
          fill="#F5C33B"
          class="antenna-spark"
        />

        <!-- corona / diadema (fase 6+) -->
        <g v-if="phaseIndex >= 5">
          <path
            d="M46 40 Q60 32 74 40 Q60 36 46 40Z"
            fill="url(#pet-gold-grad)"
            stroke="#C9922A"
            stroke-width="0.8"
          />
          <circle
            cx="60"
            cy="37"
            r="2"
            fill="#FFFFFF"
            opacity="0.9"
          />
        </g>

        <!-- pies -->
        <ellipse
          cx="48"
          cy="96"
          rx="7"
          ry="4"
          fill="url(#pet-body-grad)"
          stroke="rgba(90,120,200,0.3)"
          stroke-width="1"
        />
        <ellipse
          cx="72"
          cy="96"
          rx="7"
          ry="4"
          fill="url(#pet-body-grad)"
          stroke="rgba(90,120,200,0.3)"
          stroke-width="1"
        />
      </svg>
    </div>
  </div>
</template>

<style scoped>
.pet-stage {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pet-body {
  animation: pet-float 4s ease-in-out infinite;
}

.pet-bob {
  transform-origin: 50% 90%;
}

.pet-happy .pet-body {
  animation-duration: 3s;
}

.pet-thrilled .pet-bob {
  animation: pet-bob 0.6s ease-in-out infinite;
}

.pet-thrilled .pet-body {
  animation-duration: 1.6s;
}

.pet-aura {
  background: radial-gradient(circle, rgba(251, 191, 36, 0.4) 0%, rgba(251, 191, 36, 0.1) 55%, transparent 75%);
  animation: aura-pulse 2.6s ease-in-out infinite;
  filter: blur(6px);
}

.aura-strong {
  animation: aura-pulse-strong 1.1s ease-in-out infinite;
}

.pet-orbit {
  animation: orbit-spin 9s linear infinite;
}

.pet-wings {
  animation: wing-flap 0.9s ease-in-out infinite;
  transform-origin: center bottom;
}

.pet-sparkles .sparkle-a {
  animation: twinkle 2.4s ease-in-out infinite;
}
.pet-sparkles .sparkle-b {
  animation: twinkle 1.8s ease-in-out infinite;
  animation-delay: 0.4s;
}
.pet-sparkles .sparkle-c {
  animation: twinkle 2.1s ease-in-out infinite;
  animation-delay: 0.8s;
}

.eyes-idle {
  animation: eyes-idle 4s ease-in-out infinite;
}

.antenna-spark {
  animation: spark-glow 2.2s ease-in-out infinite;
}

@keyframes pet-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

@keyframes pet-bob {
  0%, 100% { transform: scale(1) translateY(0); }
  25% { transform: scale(1.04) translateY(-3px); }
  50% { transform: scale(0.98) translateY(0); }
  75% { transform: scale(1.02) translateY(-1px); }
}

@keyframes aura-pulse {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.08); }
}

@keyframes aura-pulse-strong {
  0%, 100% { opacity: 0.7; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.22); }
}

@keyframes orbit-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes wing-flap {
  0%, 100% { transform: scaleY(1) rotate(0deg); }
  50% { transform: scaleY(1.12) rotate(-3deg); }
}

@keyframes twinkle {
  0%, 100% { opacity: 0.3; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1.2); }
}

@keyframes spark-glow {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; filter: drop-shadow(0 0 4px rgba(245, 195, 59, 0.9)); }
}

@keyframes eyes-idle {
  0%, 78%, 100% { transform: scaleY(1); }
  80%, 90% { transform: scaleY(0.12); }
}
</style>
