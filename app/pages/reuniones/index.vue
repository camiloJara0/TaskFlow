<script setup lang="ts">
import type { Reunion } from '~/types/api'
import ReunionForm from '~/components/forms/ReunionForm.vue'

const { reunions, loading, refresh } = useReunions()

const showForm = ref(false)
const activeTab = ref('proximas')

onMounted(async () => {
  await refresh()
})

function meetingDate(r: Reunion): number {
  const date = `${r.fecha || ''}T${formatReunionTime(r.hora) || '00:00'}`
  const t = new Date(date).getTime()
  return Number.isNaN(t) ? 0 : t
}

const upcoming = computed(() => reunions.value
  .filter(r => !r.archivada && !['finalizada', 'cancelada'].includes(reunionEstadoKey(r.estado)))
  .sort((a, b) => meetingDate(a) - meetingDate(b)))

const past = computed(() => reunions.value
  .filter(r => !r.archivada && ['finalizada', 'cancelada'].includes(reunionEstadoKey(r.estado)))
  .sort((a, b) => meetingDate(b) - meetingDate(a)))

const archived = computed(() => reunions.value
  .filter(r => r.archivada)
  .sort((a, b) => meetingDate(b) - meetingDate(a)))

const tabs = [
  { label: 'Próximas', id: 'proximas' },
  { label: 'Finalizadas', id: 'finalizadas' },
  { label: 'Archivadas', id: 'archivadas' }
]

const visibleList = computed(() => {
  if (activeTab.value === 'finalizadas') return past.value
  if (activeTab.value === 'archivadas') return archived.value
  return upcoming.value
})

const currentCount = computed(() => visibleList.value.length)

async function handleSaved() {
  showForm.value = false
  await refresh(undefined, true)
}

function join(reunion: Reunion) {
  if (reunion.url) window.open(reunion.url, '_blank')
}
</script>

<template>
  <div class="p-6 sm:p-8 max-w-5xl mx-auto space-y-6 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Planificación</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Reuniones
        </h1>
        <p class="text-sm text-muted mt-1">
          {{ currentCount }} {{ activeTab === 'proximas' ? 'próximas' : activeTab === 'finalizadas' ? 'finalizadas' : 'archivadas' }}
        </p>
      </div>
      <UModal
        v-model:open="showForm"
        :ui="{ content: 'glass-panel rounded-lg overflow-hidden' }"
      >
        <UButton
          label="Nueva reunión"
          icon="i-lucide-plus"
          size="sm"
          color="primary"
          variant="solid"
          class="rounded-xl shadow-lg shadow-blue-500/25"
        />
        <template #header>
          <div>
            <h3 class="text-base font-heading font-semibold">
              Nueva reunión
            </h3>
          </div>
        </template>
        <template #body>
          <div class="p-4">
            <ReunionForm
              @saved="handleSaved"
              @cancelled="showForm = false"
            />
          </div>
        </template>
      </UModal>
    </div>

    <div class="flex items-center gap-1 p-1 glass-chip rounded-xl w-fit flex-wrap">
      <UButton
        v-for="tab in tabs"
        :key="tab.id"
        :label="tab.label"
        size="xs"
        color="neutral"
        :variant="activeTab === tab.id ? 'solid' : 'ghost'"
        class="rounded-lg"
        @click="activeTab = tab.id"
      />
    </div>

    <div
      v-if="loading && reunions.length === 0"
      class="space-y-2.5"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="p-4 rounded-2xl glass-card animate-pulse"
      >
        <div class="h-3 w-1/3 rounded bg-white/50 dark:bg-white/10" />
        <div class="h-3 w-2/3 rounded bg-white/40 dark:bg-white/5 mt-2" />
      </div>
    </div>

    <div
      v-else
      class="space-y-2.5"
    >
      <div
        v-for="reunion in visibleList"
        :key="reunion.id"
        class="group flex items-center gap-4 p-4 sm:p-5 rounded-2xl glass-card cursor-pointer transition-all duration-200 hover:translate-x-0.5"
        @click="navigateTo(`/reuniones/${reunion.id}`)"
      >
        <div
          class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center bg-violet-500/10 ring-1 ring-violet-500/20"
        >
          <UIcon
            name="i-lucide-video"
            class="w-5 h-5 text-violet-500"
          />
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="text-sm font-medium text-highlighted truncate">
              {{ reunion.titulo }}
            </p>
            <UBadge
              :color="reunionEstadoBadgeColor(reunion.estado)"
              size="xs"
              variant="subtle"
            >
              {{ reunionEstadoLabel(reunion.estado) }}
            </UBadge>
          </div>
          <div class="flex items-center gap-2 text-xs text-muted mt-1 flex-wrap">
            <span class="inline-flex items-center gap-1">
              <UIcon
                name="i-lucide-calendar"
                class="w-3 h-3"
              />
              {{ reunionDayLabel(reunion.fecha) }}
            </span>
            <span class="inline-flex items-center gap-1">
              <UIcon
                name="i-lucide-clock"
                class="w-3 h-3"
              />
              {{ formatReunionTime(reunion.hora) }}
            </span>
            <span
              v-if="reunion.espacioTrabajo?.nombre"
              class="inline-flex items-center gap-1"
            >
              <UIcon
                name="i-lucide-folder-kanban"
                class="w-3 h-3"
              />
              {{ reunion.espacioTrabajo.nombre }}
            </span>
            <span
              v-if="reunionIntegrantesNames(reunion)"
              class="truncate"
            >
              {{ reunionIntegrantesNames(reunion) }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <UButton
            v-if="reunionCanJoin(reunion)"
            label="Unirse"
            icon="i-lucide-video"
            size="xs"
            color="primary"
            variant="solid"
            class="rounded-lg"
            @click.stop="join(reunion)"
          />
          <UButton
            icon="i-lucide-chevron-right"
            size="xs"
            color="neutral"
            variant="ghost"
            class="rounded-lg"
            :to="`/reuniones/${reunion.id}`"
          />
        </div>
      </div>

      <div
        v-if="visibleList.length === 0"
        class="p-12 text-center glass-card rounded-2xl"
      >
        <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
          <UIcon
            name="i-lucide-calendar-x"
            class="w-6 h-6 text-muted"
          />
        </div>
        <p class="text-sm font-medium text-highlighted">
          No hay reuniones aquí
        </p>
        <p class="text-sm text-muted mt-1">
          Crea una nueva reunión para coordinar con tu equipo.
        </p>
      </div>
    </div>
  </div>
</template>
