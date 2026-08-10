<script setup lang="ts">
import type { Reunion } from '~/types/api'
import ReunionForm from '~/components/forms/ReunionForm.vue'

const route = useRoute()
const { showSuccess } = useApi()
const { reunions, refresh } = useReunions()

const reunion = ref<Reunion | null>(null)
const loading = ref(true)
const showEdit = ref(false)
const showDelete = ref(false)

const reunionId = computed(() => (typeof route.params.id === 'string' ? Number(route.params.id) : null))

async function load() {
  if (!reunionId.value) return
  loading.value = true
  try {
    const found = reunions.value.find(r => r.id === reunionId.value)
    if (found) {
      reunion.value = found
      return
    }
    await refresh()
    const again = reunions.value.find(r => r.id === reunionId.value)
    if (again) {
      reunion.value = again
      return
    }
    const res = await useReunionsService().getById(reunionId.value)
    reunion.value = res.data
  } finally {
    loading.value = false
  }
}

onMounted(load)

const estadoItems = [
  { label: 'Pendiente', value: 'pendiente' },
  { label: 'Confirmada', value: 'confirmada' },
  { label: 'En curso', value: 'en_curso' },
  { label: 'Finalizada', value: 'finalizada' },
  { label: 'Cancelada', value: 'cancelada' }
]

async function changeEstado(estado: string) {
  if (!reunion.value) return
  const reunionsService = useReunionsService()
  await reunionsService.changeStatus(reunion.value.id, estado)
  reunion.value.estado = reunionEstadoKey(estado)
  showSuccess('Estado actualizado')
}

async function toggleArchive() {
  if (!reunion.value) return
  const reunionsService = useReunionsService()
  await reunionsService.toggleArchive(reunion.value.id)
  reunion.value.archivada = !reunion.value.archivada
  showSuccess(reunion.value.archivada ? 'Reunión archivada' : 'Reunión restaurada')
}

async function confirmDelete() {
  if (!reunion.value) return
  const reunionsService = useReunionsService()
  await reunionsService.remove(reunion.value.id)
  showDelete.value = false
  showSuccess('Reunión eliminada')
  await navigateTo('/reuniones')
}

async function handleSaved() {
  showEdit.value = false
  await refresh(undefined, true)
  await load()
}

function join() {
  if (reunion.value?.url) window.open(reunion.value.url, '_blank')
}
</script>

<template>
  <div class="p-6 sm:p-8 max-w-3xl mx-auto space-y-6 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <NuxtLink
            to="/reuniones"
            class="text-sm text-muted hover:text-default transition-colors"
          >Reuniones 2222</NuxtLink>
          <UIcon
            name="i-lucide-chevron-right"
            class="w-3 h-3 text-muted"
          />
          <span class="text-sm font-heading font-semibold text-highlighted">{{ reunion?.titulo || 'Detalle' }}</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          {{ reunion?.titulo || 'Cargando...' }}
        </h1>
      </div>
      <div
        v-if="reunion"
        class="flex items-center gap-2"
      >
        <UButton
          v-if="reunionCanJoin(reunion)"
          label="Unirse"
          icon="i-lucide-video"
          size="sm"
          color="primary"
          variant="solid"
          class="rounded-xl shadow-lg shadow-violet-500/25"
          @click="join"
        />
        <UDropdownMenu
          :items="[
            [
              {
                label: 'Cambiar estado',
                icon: 'i-lucide-arrow-right-left',
                children: estadoItems.map(s => ({
                  label: s.label,
                  onSelect: () => changeEstado(s.value)
                }))
              }
            ],
            [
              {
                label: 'Editar',
                icon: 'i-lucide-pencil',
                onSelect: () => { showEdit = true }
              },
              {
                label: reunion.archivada ? 'Restaurar' : 'Archivar',
                icon: 'i-lucide-archive',
                onSelect: toggleArchive
              },
              {
                label: 'Eliminar',
                icon: 'i-lucide-trash-2',
                color: 'error',
                onSelect: () => { showDelete = true }
              }
            ]
          ]"
          :content="{ align: 'end' }"
        >
          <UButton
            icon="i-lucide-more-horizontal"
            size="sm"
            color="neutral"
            variant="ghost"
            class="rounded-xl"
          />
        </UDropdownMenu>
      </div>
    </div>

    <div
      v-if="loading && !reunion"
      class="space-y-2.5"
    >
      <div class="p-6 rounded-2xl glass-card animate-pulse">
        <div class="h-4 w-1/3 rounded bg-white/50 dark:bg-white/10" />
        <div class="h-4 w-2/3 rounded bg-white/40 dark:bg-white/5 mt-3" />
      </div>
    </div>

    <template v-else-if="reunion">
      <div class="rounded-2xl glass-card p-6 space-y-6">
        <div class="flex items-center gap-2 flex-wrap">
          <UBadge
            :color="reunionEstadoBadgeColor(reunion.estado)"
            variant="subtle"
          >
            {{ reunionEstadoLabel(reunion.estado) }}
          </UBadge>
          <span
            v-if="reunion.archivada"
            class="text-xs text-muted"
          >Archivada</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/50 dark:bg-white/5 flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-calendar"
                class="w-4 h-4 text-muted"
              />
            </div>
            <div>
              <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em]">
                Fecha
              </p>
              <p class="text-sm font-medium text-highlighted">
                {{ reunionDayLabel(reunion.fecha) }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/50 dark:bg-white/5 flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-clock"
                class="w-4 h-4 text-muted"
              />
            </div>
            <div>
              <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em]">
                Hora
              </p>
              <p class="text-sm font-medium text-highlighted">
                {{ formatReunionTime(reunion.hora) }}
              </p>
            </div>
          </div>
          <div
            v-if="reunion.espacioTrabajo?.nombre"
            class="flex items-center gap-3"
          >
            <div class="w-10 h-10 rounded-xl bg-white/50 dark:bg-white/5 flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-folder-kanban"
                class="w-4 h-4 text-muted"
              />
            </div>
            <div>
              <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em]">
                Espacio
              </p>
              <NuxtLink
                :to="`/workspaces/${reunion.espacio_trabajo_id}`"
                class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
              >
                {{ reunion.espacioTrabajo.nombre }}
              </NuxtLink>
            </div>
          </div>
          <div
            v-if="reunion.creador?.nombre"
            class="flex items-center gap-3"
          >
            <div class="w-10 h-10 rounded-xl bg-white/50 dark:bg-white/5 flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-user"
                class="w-4 h-4 text-muted"
              />
            </div>
            <div>
              <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em]">
                Organizador
              </p>
              <p class="text-sm font-medium text-highlighted">
                {{ reunion.creador.nombre }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="reunion.descripcion">
          <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em] mb-1.5">
            Descripción
          </p>
          <p class="text-sm text-muted whitespace-pre-wrap">
            {{ reunion.descripcion }}
          </p>
        </div>

        <div v-if="reunion.url">
          <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em] mb-1.5">
            Enlace
          </p>
          <a
            :href="reunion.url"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-violet-600 dark:text-violet-400 hover:underline break-all"
          >
            <UIcon
              name="i-lucide-video"
              class="w-4 h-4 shrink-0"
            />
            {{ reunion.url }}
          </a>
        </div>

        <div>
          <p class="text-[10px] font-bold text-muted uppercase tracking-[0.14em] mb-2">
            Integrantes
          </p>
          <div class="flex items-center -space-x-2">
            <UAvatar
              v-for="integrante in reunion.integrantes || []"
              :key="integrante.id"
              :src="integrante.usuario?.foto || undefined"
              :text="(integrante.usuario?.nombre || '?').slice(0, 2).toUpperCase()"
              size="sm"
              class="ring-2 ring-white dark:ring-bg rounded-full"
            />
            <span
              v-if="!reunion.integrantes?.length"
              class="text-xs text-muted"
            >Solo tú</span>
          </div>
        </div>
      </div>
    </template>

    <div
      v-else
      class="p-12 text-center glass-card rounded-2xl"
    >
      <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
        <UIcon
          name="i-lucide-calendar-x"
          class="w-6 h-6 text-muted"
        />
      </div>
      <p class="text-sm text-muted">
        No se encontró la reunión
      </p>
    </div>

    <UModal
      v-model:open="showEdit"
      :ui="{ content: 'glass-panel rounded-lg overflow-hidden' }"
    >
      <template #header>
        <div>
          <h3 class="text-base font-heading font-semibold">
            Editar reunión
          </h3>
        </div>
      </template>
      <template #body>
        <div class="p-4">
          <ReunionForm
            v-if="reunion"
            :reunion="reunion"
            @saved="handleSaved"
            @cancelled="showEdit = false"
          />
        </div>
      </template>
    </UModal>

    <UModal v-model:open="showDelete">
      <template #header>
        <h3 class="text-base font-heading font-semibold">
          Eliminar reunión
        </h3>
      </template>
      <template #body>
        <p class="text-sm text-muted">
          ¿Estás seguro de que deseas eliminar la reunión "{{ reunion?.titulo }}"? Esta acción no se puede deshacer.
        </p>
      </template>
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton
            label="Cancelar"
            size="sm"
            color="neutral"
            variant="ghost"
            @click="showDelete = false"
          />
          <UButton
            label="Eliminar"
            size="sm"
            color="error"
            variant="solid"
            @click="confirmDelete"
          />
        </div>
      </template>
    </UModal>
  </div>
</template>
