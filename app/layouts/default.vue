<script setup lang="ts">
const { sidebarOpen, sidebarCollapsed } = provideSidebar()

const { backgroundImage } = useAppearance()

const taskPanelOpen = ref(false)
const selectedTask = ref<null | Record<string, any>>(null)
const taskRefreshKey = useState('task-refresh-key', () => 0)
const { registerTaskCompletion } = useGamification()

function openTask(task: Record<string, any>) {
  selectedTask.value = task
  taskPanelOpen.value = true
}

function onTaskSaved(task: Record<string, any>) {
  if (statusKey(task?.estado) === 'done') {
    registerTaskCompletion(task)
  }
  selectedTask.value = task
  taskRefreshKey.value++
}

provide('openTask', openTask)
</script>

<template>
  <div class="min-h-screen flex">
    <UDashboardSidebar v-model:open="sidebarOpen" v-model:collapsed="sidebarCollapsed" collapsible :ui="{
      root: 'glass-sidebar border-r border-white/60 dark:border-white/5',
      body: 'overflow-y-auto scrollbar-thin',
      content: 'backdrop-blur-2xl'
    }" mode="slideover" :toggle="{color: 'neutral', onClick: () => {sidebarOpen = !sidebarOpen}}">
      <AppSidebar />
    </UDashboardSidebar>

    <UDashboardPanel class="h-screen">
      <!-- NavBar -->
      <UDashboardNavbar class="xl:hidden" :ui="{ root: 'glass-nav h-14' }" :toggle="{color: 'neutral', onClick: () => {sidebarOpen = !sidebarOpen}}">
        <template #leading>
          <AppTopbar />
        </template>
      </UDashboardNavbar>

      <div class="w-full flex-1 overflow-y-auto scrollbar-thin bg-cover bg-center" :style="backgroundImage ? { backgroundImage: `url('${backgroundImage}')` } : undefined">
        <NuxtPage @open-task="openTask" />
      </div>
    </UDashboardPanel>

    <USlideover v-model:open="taskPanelOpen" side="right"
      :ui="{ content: 'w-[600px] max-w-[90vw] glass-panel rounded-l-xl border-l pl-8 border-white/70 dark:border-white/10' }">
      <template #content>
        <AppTaskPanel v-if="selectedTask" :key="selectedTask.id" :task="selectedTask" @close="taskPanelOpen = false"
          @saved="onTaskSaved" />
      </template>
    </USlideover>

    <GamificationOverlay />
  </div>
</template>
