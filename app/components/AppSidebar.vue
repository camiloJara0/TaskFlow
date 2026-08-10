<script setup lang="ts">
import TeamWorkspaceForm from '~/components/forms/TeamWorkspaceForm.vue'

const { sidebarCollapsed: collapsed } = useSidebar()

interface User {
  nombre?: string
  email?: string
  foto?: string
}

const mainNav = [
  { label: 'Dashboard', icon: 'i-lucide-layout-dashboard', to: '/dashboard' },
  { label: 'Mis tareas', icon: 'i-lucide-check-square', to: '/tasks' },
  { label: 'Reuniones', icon: 'i-lucide-video', to: '/reuniones' },
  { label: 'Espacios de trabajo', icon: 'i-lucide-folder-kanban', to: '/workspaces' },
  { label: 'Progreso', icon: 'i-lucide-trophy', to: '/progress' },
  { label: 'Notificaciones', icon: 'i-lucide-bell', to: '/notifications' },
  { label: 'Configuración', icon: 'i-lucide-settings', to: '/settings' }
]

const offlineStore = useOfflineStore()
const workspaces = computed(() => (offlineStore.collections.workspaces ?? []) as any[])
const showWorkspaceForm = ref(false)
const { getAll } = useWorkspacesService()

async function fetchWorkspaces() {
  await offlineStore.loadCollection('workspaces', () => getAll(), { force: true })
}

onMounted(async () => {
  await offlineStore.loadCollection('workspaces', () => getAll())
})

function handleSaved() {
  showWorkspaceForm.value = false
  fetchWorkspaces()
}
const user = useState<User | null>('user', () => null)

onMounted(() => {
  const userLocal = localStorage.getItem('user')

  if (userLocal) {
    user.value = JSON.parse(userLocal)
  }
})
</script>

<template>
  <div
    class="flex flex-col h-full gap-0.5 py-3"
    :class="collapsed ? 'px-2.5' : 'px-3'"
  >
    <div
      class="flex items-center h-11 shrink-0 mb-2"
      :class="collapsed ? 'justify-center px-0' : 'px-1'"
    >
      <div
        v-if="!collapsed"
        class="flex items-center gap-2.5"
      >
        <div class="relative">
          <div
            class="w-8 h-8 rounded-xl bg-linear-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/30"
          >
            <span class="text-white font-extrabold text-sm">T</span>
          </div>
          <div class="absolute -inset-1 rounded-xl bg-blue-500/30 blur-lg -z-10" />
        </div>
        <div>
          <span
            class="font-heading font-bold text-[15px] tracking-tight text-highlighted leading-none block"
          >TaskFlow</span>
          <span class="text-[10px] text-muted font-medium tracking-wide">Productividad</span>
        </div>
      </div>
      <div
        v-else
        class="relative"
      >
        <div
          class="w-8 h-8 rounded-xl bg-linear-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-500/30"
        >
          <span class="text-white font-extrabold text-sm">T</span>
        </div>
        <div class="absolute -inset-1 rounded-xl bg-blue-500/30 blur-lg -z-10" />
      </div>
    </div>

    <div class="space-y-0.5 mt-1">
      <div
        v-for="item in mainNav"
        :key="item.to"
      >
        <UTooltip
          v-if="collapsed"
          :text="item.label"
          side="right"
          :delay="300"
        >
          <NuxtLink
            v-slot="{ isActive: active }"
            :to="item.to"
            custom
          >
            <div
              class="flex items-center justify-center w-full py-2 rounded-xl transition-all duration-200 cursor-pointer"
              :class="active
                ? 'bg-linear-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/25'
                : 'text-muted hover:text-default hover:bg-white/60 dark:hover:bg-white/6'"
            >
              <UIcon
                :name="item.icon"
                class="w-4 h-4"
              />
            </div>
          </NuxtLink>
        </UTooltip>
        <nuxt-link
          v-else
          v-slot="{ isActive: active }"
          :to="item.to"
          custom
        >
          <nuxt-link
            :to="item.to"
            class="relative flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition-all duration-200 cursor-pointer"
            :class="active
              ? 'bg-linear-to-br from-blue-500 to-blue-600 text-white font-semibold shadow-lg shadow-blue-500/25'
              : 'text-muted hover:text-default hover:bg-white/60 dark:hover:bg-white/6'"
          >
            <UIcon
              :name="item.icon"
              class="w-4 h-4 shrink-0"
            />
            <span class="truncate">{{ item.label }}</span>
          </nuxt-link>
        </nuxt-link>
      </div>
    </div>

    <template v-if="!collapsed">
      <div class="mt-4">
        <div class="flex items-center justify-between px-3 mb-2">
          <span class="text-[10px] font-bold text-muted uppercase tracking-[0.14em]">Espacios</span>
          <UModal v-model:open="showWorkspaceForm">
            <UButton
              icon="i-lucide-plus"
              size="sm"
              color="neutral"
              variant="ghost"
            />
            <template #header>
              <h3 class="text-lg font-semibold text-highlighted">
                Crear nuevo espacio de trabajo
              </h3>
            </template>
            <template #body>
              <TeamWorkspaceForm
                @saved="handleSaved"
                @cancelled="showWorkspaceForm = false"
              />
            </template>
          </UModal>
        </div>
        <div class="space-y-0.5">
          <NuxtLink
            v-for="ws in workspaces"
            v-slot="{ isActive: active }"
            :key="ws.id"
            :to="`/workspaces/${ws.id}`"
            custom
          >
            <nuxt-link
              :to="`/workspaces/${ws.id}`"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition-all duration-200 cursor-pointer"
              :class="active
                ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium ring-1 ring-blue-500/20'
                : 'text-muted hover:text-default hover:bg-white/60 dark:hover:bg-white/6'"
            >
              <UIcon
                :name="ws.icono"
                class="w-4 h-4 shrink-0"
                :style="{ backgroundColor: ws.color }"
              />
              <span class="truncate">{{ ws.nombre }}</span>
            </nuxt-link>
          </NuxtLink>
        </div>
      </div>
    </template>

    <div class="mt-auto pt-3 border-t border-white/50 dark:border-white/10">
      <NuxtLink
        v-if="!collapsed"
        v-slot="{ isActive: active }"
        to="/settings"
        custom
      >
        <div
          class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-sm transition-all duration-200 cursor-pointer"
          :class="active
            ? 'bg-blue-500/10 ring-1 ring-blue-500/20'
            : 'hover:bg-white/60 dark:hover:bg-white/6'"
        >
          <div class="relative shrink-0">
            <UAvatar
              :src="user?.foto || `https://api.dicebear.com/9.x/initials/svg?seed=${(user?.nombre?.charAt(0)) ?? ''}&backgroundColor=2563eb&textColor=ffffff`"
              size="xs"
            />
            <div
              class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-bg"
            />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ user?.nombre }}</p>
            <p class="text-[10px] text-muted truncate">{{ user?.email }}</p>
          </div>
          <UButton
            to="/auth/login"
            icon="i-lucide-arrow-left-from-line"
            variant="ghost"
            color="error"
          />
        </div>
      </NuxtLink>
      <UTooltip
        v-else
        text="Perfil"
        side="right"
        :delay="300"
      >
        <NuxtLink
          to="/settings"
          class="flex justify-center py-1"
        >
          <div class="relative">
            <UAvatar
              src="https://api.dicebear.com/9.x/initials/svg?seed=CM&backgroundColor=2563eb&textColor=ffffff"
              size="xs"
            />
            <div
              class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-bg"
            />
          </div>
        </NuxtLink>
      </UTooltip>
    </div>
  </div>
</template>
