<script setup lang="ts">
interface Particle {
  id: number
  left: number
  delay: number
  duration: number
  color: string
  size: number
  rotation: number
  shape: 'rect' | 'circle'
  drift: number
}

const props = withDefaults(defineProps<{
  burst?: number
  count?: number
}>(), {
  burst: 0,
  count: 60
})

const particles = ref<Particle[]>([])
let seed = 0

const COLORS = ['#F5C33B', '#FDE68A', '#60A5FA', '#93B4FD', '#38BDF8', '#C7DBFF', '#F9A8C9']

function spawn() {
  const list: Particle[] = []
  for (let i = 0; i < props.count; i++) {
    list.push({
      id: seed++,
      left: Math.random() * 100,
      delay: Math.random() * 0.35,
      duration: 1.1 + Math.random() * 1.1,
      color: COLORS[Math.floor(Math.random() * COLORS.length)]!,
      size: 5 + Math.random() * 6,
      rotation: Math.random() * 360,
      shape: Math.random() > 0.45 ? 'rect' : 'circle',
      drift: (Math.random() - 0.5) * 60
    })
  }
  particles.value = list
  setTimeout(() => {
    particles.value = []
  }, 3200)
}

watch(() => props.burst, (v) => {
  if (v > 0) spawn()
})
</script>

<template>
  <div class="pointer-events-none fixed inset-0 z-[90] overflow-hidden">
    <div
      v-for="p in particles"
      :key="p.id"
      class="confetti-piece absolute top-0"
      :style="{
        'left': `${p.left}%`,
        'width': `${p.size}px`,
        'height': p.shape === 'rect' ? `${p.size * 0.55}px` : `${p.size}px`,
        'backgroundColor': p.color,
        'borderRadius': p.shape === 'circle' ? '9999px' : '2px',
        '--drift': `${p.drift}px`,
        'animationDuration': `${p.duration}s`,
        'animationDelay': `${p.delay}s`,
        '--rot': `${p.rotation}deg`
      }"
    />
  </div>
</template>

<style scoped>
.confetti-piece {
  animation: confetti-fall cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
  opacity: 0;
}

@keyframes confetti-fall {
  0% {
    opacity: 1;
    transform: translate3d(0, -4vh, 0) rotate(0deg);
  }
  100% {
    opacity: 0.9;
    transform: translate3d(var(--drift), 104vh, 0) rotate(var(--rot));
  }
}
</style>
