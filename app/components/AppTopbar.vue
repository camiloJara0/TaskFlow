<script setup lang="ts">
import type { Notification } from '~/types/api'

const searchOpen = ref(false)
const notificationsOpen = ref(false)

const { notifications, loading, unreadCount, markRead, markAll, refresh } = useNotifications()

const previewNotifications = computed(() => notifications.value.slice(0, 6))

async function openNotification(n: Notification) {
  notificationsOpen.value = false
  if (!n.leida) {
    await markRead(n.id)
  }
  const url = notificationUrl(n)
  if (url) {
    navigateTo(url)
  }
}

onMounted(async () => {
  await refresh()
})
</script>

<template>

  <div class="flex items-center justify-between gap-1.5 w-full px-4">
    <div class="flex items-center gap-2.5">
      <div class="flex items-center gap-2.5">
        <div class="w-6 h-6 rounded-lg bg-linear-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-sm shadow-blue-500/30">
          <span class="text-white font-extrabold text-[10px]">T</span>
        </div>
        <span class="text-sm font-heading font-bold tracking-tight text-highlighted">TaskFlow</span>
      </div>
      <div class="hidden sm:block w-px h-5 bg-white/50 dark:bg-white/10" />
      <UBreadcrumb>
        <NuxtLink
          to="/dashboard"
          class="text-sm text-muted hover:text-default transition-colors"
        >
          Dashboard
        </NuxtLink>
      </UBreadcrumb>
    </div>

    <div class="flex-1" />

    <!-- <button
      class="hidden md:flex items-center gap-2 px-3 h-8 rounded-xl glass-chip text-muted text-xs w-48 hover:text-default cursor-pointer"
      @click="searchOpen = !searchOpen"
    >
      <UIcon
        name="i-lucide-search"
        class="w-3.5 h-3.5"
      />
      <span class="flex-1 text-left">Buscar…</span>
      <UKbd size="sm">
        ⌘K
      </UKbd>
    </button> -->

    <UButton
      icon="i-lucide-search"
      size="sm"
      color="neutral"
      variant="ghost"
      class="shrink-0 text-muted md:hidden"
      @click="searchOpen = !searchOpen"
    />

    <UPopover
      v-model:open="notificationsOpen"
      :ui="{ content: 'w-80 p-0 glass-panel rounded-lg overflow-hidden' }"
    >
      <UButton
        icon="i-lucide-bell"
        size="sm"
        color="neutral"
        variant="ghost"
        class="shrink-0 relative text-muted hover:text-default"
      >
        <UBadge
          v-if="unreadCount > 0"
          size="xs"
          color="error"
          class="absolute -top-0.5 -right-0.5"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </UBadge>
      </UButton>

      <template #content>
        <div class="p-3.5 border-b border-default">
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold">Notificaciones</span>
            <div class="flex items-center gap-1">
              <UButton
                v-if="unreadCount > 0"
                :label="`Marcar todas`"
                icon="i-lucide-check-check"
                size="xs"
                color="neutral"
                variant="ghost"
                @click="markAll()"
              />
              <UButton
                label="Ver todas"
                size="xs"
                color="neutral"
                variant="ghost"
                to="/notifications"
                @click="notificationsOpen = false"
              />
            </div>
          </div>
        </div>
        <div
          v-if="loading && notifications.length === 0"
          class="p-6 text-center text-muted"
        >
          <UIcon
            name="i-lucide-loader-2"
            class="w-5 h-5 mx-auto animate-spin"
          />
        </div>
        <div
          v-else-if="previewNotifications.length === 0"
          class="p-6 text-center"
        >
          <UIcon
            name="i-lucide-bell-off"
            class="w-5 h-5 mx-auto mb-2 text-muted"
          />
          <p class="text-xs text-muted">
            No hay notificaciones
          </p>
        </div>
        <div
          v-else
          class="max-h-80 overflow-y-auto scrollbar-thin"
        >
          <div
            v-for="n in previewNotifications"
            :key="n.id"
            class="flex items-start gap-3 p-3 hover:bg-white/60 dark:hover:bg-white/5 cursor-pointer transition-colors border-b border-default last:border-0"
            @click="openNotification(n)"
          >
            <div class="relative">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/60 dark:bg-white/5">
                <UIcon
                  :name="notificationStyle(n.tipo).icon"
                  class="w-4 h-4 shrink-0"
                  :class="notificationStyle(n.tipo).color"
                />
              </div>
              <div
                v-if="!n.leida"
                class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-blue-500 rounded-full ring-2 ring-white dark:ring-bg"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p
                class="text-sm truncate"
                :class="n.leida ? 'font-medium' : 'font-semibold'"
              >
                {{ n.titulo }}
              </p>
              <p class="text-xs text-muted truncate">
                {{ n.mensaje }}
              </p>
            </div>
            <span class="text-[10px] text-muted shrink-0">{{ notificationTimeAgo(n.created_at) }}</span>
          </div>
        </div>
        <div class="p-2 border-t border-default">
          <UButton
            label="Ver todas las notificaciones"
            block
            size="sm"
            color="neutral"
            variant="ghost"
            to="/notifications"
            @click="notificationsOpen = false"
          />
        </div>
      </template>
    </UPopover>

    <UDropdownMenu
      :ui="{ content: 'glass-panel rounded-lg overflow-hidden' }"
      :items="[
        [{ label: 'Perfil', icon: 'i-lucide-user', to: '/settings' }],
        [{ label: 'Configuración', icon: 'i-lucide-settings', to: '/settings' }],
        [{ label: 'Cerrar sesión', icon: 'i-lucide-log-out', color: 'error' }]
      ]"
    >
      <div class="relative shrink-0">
        <UAvatar
          src="https://api.dicebear.com/9.x/initials/svg?seed=CM&backgroundColor=2563eb&textColor=ffffff"
          size="sm"
          class="cursor-pointer ring-2 ring-white/70 dark:ring-white/10 hover:ring-blue-400 transition-all rounded-full shadow-sm"
        />
        <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-bg" />
      </div>
    </UDropdownMenu>
  </div>

  <!-- <UCommandPalette v-model:open="searchOpen" /> -->
</template>
