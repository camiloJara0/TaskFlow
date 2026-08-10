import type { Ref } from 'vue'

interface SidebarContext {
  sidebarOpen: Ref<boolean>
  sidebarCollapsed: Ref<boolean>
}

const SIDEBAR_KEY = Symbol('sidebar')

export function provideSidebar() {
  const sidebarOpen = ref(false)
  const sidebarCollapsed = ref(false)
  provide(SIDEBAR_KEY, { sidebarOpen, sidebarCollapsed })
  return { sidebarOpen, sidebarCollapsed }
}

export function useSidebar(): SidebarContext {
  const context = inject<SidebarContext>(SIDEBAR_KEY)
  if (!context) {
    throw new Error('useSidebar must be used within a component that calls provideSidebar')
  }
  return context
}
