<script setup lang="ts">
import type { Notification } from '~/types/api'

const { notifications, loading, unreadCount, markRead, markAll, remove } = useNotifications()

const activeFilter = ref('all')

const filters = [
  { label: 'Todas', id: 'all' },
  { label: 'No leídas', id: 'unread' },
  { label: 'Menciones', id: 'mentions' },
  { label: 'Comentarios', id: 'comments' },
  { label: 'Reuniones', id: 'reunions' }
]

const filteredNotifications = computed(() => {
  const list = notifications.value
  if (activeFilter.value === 'unread') return list.filter(n => !n.leida)
  if (activeFilter.value === 'mentions') return list.filter(n => notificationKind(n.tipo) === 'mencion')
  if (activeFilter.value === 'comments') return list.filter(n => notificationKind(n.tipo) === 'comentario')
  if (activeFilter.value === 'reunions') return list.filter(n => notificationKind(n.tipo) === 'reunion')
  return list
})

function groupLabel(item: Notification): string {
  const date = new Date(item.created_at)
  if (Number.isNaN(date.getTime())) return 'Anteriores'

  const startToday = new Date()
  startToday.setHours(0, 0, 0, 0)
  const startYesterday = new Date(startToday)
  startYesterday.setDate(startToday.getDate() - 1)

  if (date >= startToday) return 'Hoy'
  if (date >= startYesterday) return 'Ayer'
  if (date >= new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)) return 'Esta semana'
  return 'Anteriores'
}

const groups = computed(() => {
  const byLabel = new Map<string, Notification[]>()
  for (const n of filteredNotifications.value) {
    const label = groupLabel(n)
    if (!byLabel.has(label)) byLabel.set(label, [])
    byLabel.get(label)!.push(n)
  }
  return Array.from(byLabel.entries())
})

async function openNotification(n: Notification) {
  if (!n.leida) {
    await markRead(n.id)
  }
  const url = notificationUrl(n)
  if (url) {
    navigateTo(url)
  }
}

async function handleMarkAll() {
  await markAll()
}

async function handleRemove(n: Notification) {
  await remove(n.id)
}
</script>

<template>
  <div class="p-6 sm:p-8 max-w-3xl mx-auto space-y-8 page-enter">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Centro de avisos</span>
        </div>
        <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
          Notificaciones
        </h1>
        <p class="text-sm text-muted mt-1">
          {{ unreadCount > 0 ? `${unreadCount} sin leer` : 'Todo al día' }}
        </p>
      </div>
      <UButton
        v-if="unreadCount > 0"
        :label="`Marcar todas como leídas (${unreadCount})`"
        icon="i-lucide-check-check"
        size="xs"
        color="neutral"
        variant="ghost"
        class="rounded-lg"
        :loading="loading"
        @click="handleMarkAll"
      />
    </div>

    <div class="flex items-center gap-1 p-1 glass-chip rounded-xl w-fit flex-wrap">
      <UButton
        v-for="f in filters"
        :key="f.id"
        :label="f.label"
        size="xs"
        color="neutral"
        :variant="activeFilter === f.id ? 'solid' : 'ghost'"
        class="rounded-lg"
        @click="activeFilter = f.id"
      />
    </div>

    <div
      v-if="loading && notifications.length === 0"
      class="space-y-2.5"
    >
      <div
        v-for="i in 5"
        :key="i"
        class="flex items-start gap-4 p-4 sm:p-5 rounded-2xl glass-card animate-pulse"
      >
        <div class="w-10 h-10 rounded-xl bg-white/50 dark:bg-white/5" />
        <div class="flex-1 space-y-2 pt-1">
          <div class="h-3 w-1/3 rounded bg-white/50 dark:bg-white/10" />
          <div class="h-3 w-2/3 rounded bg-white/40 dark:bg-white/5" />
        </div>
      </div>
    </div>

    <div
      v-else-if="groups.length > 0"
      class="space-y-8"
    >
      <div
        v-for="[label, items] in groups"
        :key="label"
        class="space-y-2.5"
      >
        <div class="flex items-center gap-2.5 px-1">
          <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-muted">{{ label }}</span>
          <div class="flex-1 h-px bg-white/50 dark:bg-white/10" />
        </div>

        <div
          v-for="(n, i) in items"
          :key="n.id"
          class="group relative flex items-start gap-4 p-4 sm:p-5 rounded-2xl transition-all duration-200 cursor-pointer animate-fade-up"
          :style="{ animationDelay: `${i * 40}ms` }"
          :class="n.leida
            ? 'glass-card opacity-75 hover:opacity-100'
            : 'glass-card border-l-[3px] border-l-blue-500! shadow-blue-500/10'"
          @click="openNotification(n)"
        >
          <div class="relative shrink-0">
            <div
              class="w-10 h-10 rounded-xl flex items-center justify-center bg-white/70 dark:bg-white/5 border border-white/70 dark:border-white/10 shadow-sm"
              :class="n.leida ? '' : 'ring-2'"
            >
              <UIcon
                :name="notificationStyle(n.tipo).icon"
                class="w-4 h-4"
                :class="notificationStyle(n.tipo).color"
              />
            </div>
            <div
              v-if="!n.leida"
              class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full ring-2 ring-white dark:ring-bg animate-pulse"
            />
          </div>

          <div class="flex-1 min-w-0 pt-0.5">
            <div class="flex items-center gap-2">
              <p
                class="text-sm text-highlighted"
                :class="{ 'font-semibold': !n.leida, 'font-medium': n.leida }"
              >
                {{ n.titulo }}
              </p>
              <span class="text-[10px] text-muted ml-auto shrink-0 whitespace-nowrap">{{ notificationTimeAgo(n.created_at) }}</span>
            </div>
            <p class="text-sm text-muted mt-1 line-clamp-2">
              {{ n.mensaje }}
            </p>
          </div>

          <div class="flex flex-col items-center gap-2 shrink-0">
            <UButton
              icon="i-lucide-x"
              size="xs"
              color="neutral"
              variant="ghost"
              class="rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-muted"
              aria-label="Eliminar notificación"
              @click.stop="handleRemove(n)"
            />
            <UButton
              v-if="!n.leida"
              icon="i-lucide-check"
              size="xs"
              color="neutral"
              variant="ghost"
              class="rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-blue-500"
              aria-label="Marcar como leída"
              @click.stop="markRead(n.id)"
            />
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="p-12 text-center glass-card rounded-2xl animate-fade-up"
    >
      <div class="w-14 h-14 rounded-2xl bg-white/50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3">
        <UIcon
          :name="activeFilter === 'all' ? 'i-lucide-bell-off' : 'i-lucide-check-circle-2'"
          class="w-6 h-6 text-muted"
        />
      </div>
      <p class="text-sm font-medium text-highlighted">
        {{ activeFilter === 'all' ? 'No hay notificaciones' : 'Nada por aquí' }}
      </p>
      <p class="text-sm text-muted mt-1">
        {{ activeFilter === 'all' ? 'Las notificaciones de tu equipo aparecerán aquí.' : 'No hay resultados para este filtro.' }}
      </p>
    </div>
  </div>
</template>
