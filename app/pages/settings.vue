<script setup lang="ts">
import ProfileForm from '~/components/forms/ProfileForm.vue'

const activeSection = ref('profile')

const { soundEnabled, soundVolume, setSoundEnabled, setSoundVolume, play } = useGamification()

const { subscribed: pushSubscribed, permission: pushPermission, busy: pushBusy, error: pushError, toggle: pushToggle } = usePushNotifications()

const { fontSize, reducedMotion, backgroundImage, solidTheme } = useAppearance()

const customBg = ref('')

const backgroundPresets = [
  { label: 'Aurora (sin imagen)', value: 'p' },
  { label: 'Workspace (predeterminada)', value: 'https://images.pexels.com/photos/7135037/pexels-photo-7135037.jpeg' }
]

const fontSizes = [
  { label: 'Pequeño', value: 'small' },
  { label: 'Mediano', value: 'medium' },
  { label: 'Grande', value: 'large' }
]

function applyCustomBg() {
  backgroundImage.value = customBg.value.trim()
}

function setFontSize(value: string) {
  if (value === 'small' || value === 'medium' || value === 'large') {
    fontSize.value = value
  }
}

function setBackgroundImage(value: string) {
  backgroundImage.value = value
}

const sections = [
  { label: 'Perfil', id: 'profile', icon: 'i-lucide-user' },
  { label: 'Apariencia', id: 'appearance', icon: 'i-lucide-palette' },
  { label: 'Integraciones', id: 'integrations', icon: 'i-lucide-plug' },
  { label: 'Preferencias', id: 'preferences', icon: 'i-lucide-sliders' },
  { label: 'Sincronización', id: 'sync', icon: 'i-lucide-refresh-cw' }
]

const integrations = ref([
  { name: 'Slack', description: 'Notificaciones y mensajes', connected: true, icon: 'i-simple-icons-slack', color: 'text-[#4A154B]' },
  { name: 'GitHub', description: 'Sincronización de repositorios', connected: true, icon: 'i-simple-icons-github', color: '' },
  { name: 'Figma', description: 'Previsualización de diseños', connected: false, icon: 'i-simple-icons-figma', color: 'text-[#F24E1E]' },
  { name: 'Google Calendar', description: 'Sincronización de eventos', connected: false, icon: 'i-simple-icons-googlecalendar', color: 'text-[#4285F4]' },
  { name: 'Notion', description: 'Importación de documentos', connected: false, icon: 'i-simple-icons-notion', color: 'text-black dark:text-white' },
  { name: 'Discord', description: 'Webhooks de actividades', connected: false, icon: 'i-simple-icons-discord', color: 'text-[#5865F2]' }
])
</script>

<template>
  <div class="p-6 sm:p-8 max-w-4xl mx-auto space-y-8 page-enter">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-[0.14em]">Preferencias</span>
      </div>
      <h1 class="text-3xl font-heading font-bold tracking-tight text-highlighted">
        Configuración
      </h1>
      <p class="text-sm text-muted mt-1">
        Gestiona tu cuenta y preferencias
      </p>
    </div>

    <div class="flex gap-8 flex-col md:flex-row">
      <div class="md:w-52 shrink-0">
        <div class="glass-card rounded-lg p-2 space-y-1">
          <UButton
            v-for="s in sections"
            :key="s.id"
            :icon="s.icon"
            :label="s.label"
            size="sm"
            color="neutral"
            variant="ghost"
            class="w-full justify-start rounded-xl"
            :class="activeSection === s.id
              ? 'bg-blue-500/10! text-blue-600! dark:text-blue-400! font-medium! ring-1 ring-blue-500/20'
              : ''"
            @click="activeSection = s.id"
          />
        </div>
      </div>

      <div class="flex-1 min-w-0">
        <div
          v-if="activeSection === 'profile'"
          class="space-y-6"
        >
          <ProfileForm />
        </div>

        <div
          v-if="activeSection === 'appearance'"
          class="space-y-6"
        >
          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-moon"
                  class="w-4 h-4 text-indigo-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Tema oscuro
                </p>
                <p class="text-xs text-muted">
                  Cambia entre modo claro y oscuro
                </p>
              </div>
            </div>
            <UColorModeSwitch />
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-type"
                  class="w-4 h-4 text-amber-500"
                />
              </div>
              <label class="text-sm font-medium">Tamaño de fuente</label>
            </div>
            <USelect
              :model-value="fontSize"
              size="sm"
              :items="fontSizes"
              class="w-36"
              @update:model-value="setFontSize($event)"
            />
          </div>

          <div class="glass-card rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center">
                  <UIcon
                    name="i-lucide-image"
                    class="w-4 h-4 text-violet-500"
                  />
                </div>
                <div>
                  <p class="text-sm font-medium">
                    Imagen de fondo
                  </p>
                  <p class="text-xs text-muted">
                    Imagen detrás del contenido de la aplicación
                  </p>
                </div>
              </div>
            </div>
            <USelect
              :model-value="backgroundImage || 'p'"
              :items="backgroundPresets"
              class="w-full"
              @update:model-value="setBackgroundImage($event)"
            />
            <div class="flex gap-2">
              <UInput
                v-model="customBg"
                placeholder="URL de imagen personalizada..."
                class="flex-1"
              />
              <UButton
                label="Aplicar"
                size="sm"
                color="neutral"
                variant="outline"
                class="rounded-lg shrink-0"
                @click="applyCustomBg"
              />
            </div>
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-layers"
                  class="w-4 h-4 text-teal-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Tema sólido
                </p>
                <p class="text-xs text-muted">
                  Usa fondos sólidos en lugar de vidrio para no interferir con la imagen de fondo
                </p>
              </div>
            </div>
            <USwitch
              :model-value="solidTheme"
              @update:model-value="solidTheme = $event"
            />
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-rose-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-motion"
                  class="w-4 h-4 text-rose-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Animaciones reducidas
                </p>
                <p class="text-xs text-muted">
                  Desactiva las animaciones de la interfaz
                </p>
              </div>
            </div>
            <USwitch
              :model-value="reducedMotion"
              @update:model-value="reducedMotion = $event"
            />
          </div>
        </div>

        <div
          v-if="activeSection === 'integrations'"
          class="glass-card rounded-2xl divide-y divide-default/70 overflow-hidden"
        >
          <div
            v-for="integration in integrations"
            :key="integration.name"
            class="flex items-center justify-between p-4 hover:bg-white/50 dark:hover:bg-white/4 transition-colors"
          >
            <div class="flex items-center gap-3.5">
              <div class="w-10 h-10 rounded-xl bg-white/60 dark:bg-white/5 border border-white/70 dark:border-white/10 flex items-center justify-center">
                <UIcon
                  :name="integration.icon"
                  class="w-5 h-5"
                  :class="integration.color"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  {{ integration.name }}
                </p>
                <p class="text-xs text-muted">
                  {{ integration.description }}
                </p>
              </div>
            </div>
            <UButton
              :label="integration.connected ? 'Conectado' : 'Conectar'"
              size="xs"
              :color="integration.connected ? 'success' : 'neutral'"
              :variant="integration.connected ? 'subtle' : 'outline'"
              class="rounded-lg"
            />
          </div>
        </div>

        <div
          v-if="activeSection === 'preferences'"
          class="space-y-6"
        >
          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-mail"
                  class="w-4 h-4 text-blue-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Notificaciones por correo
                </p>
                <p class="text-xs text-muted">
                  Recibe resúmenes diarios por email
                </p>
              </div>
            </div>
            <USwitch :model-value="true" />
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-bell"
                  class="w-4 h-4 text-emerald-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Notificaciones push
                </p>
                <p class="text-xs text-muted">
                  Recibe notificaciones en tu navegador
                </p>
                <p
                  v-if="pushError"
                  class="text-xs text-red-500 mt-0.5"
                >
                  {{ pushError }}
                </p>
                <p
                  v-else-if="pushPermission === 'denied'"
                  class="text-xs text-amber-500 mt-0.5"
                >
                  Permiso denegado. Habilítalo desde el navegador.
                </p>
              </div>
            </div>
            <USwitch
              :model-value="pushSubscribed"
              :loading="pushBusy"
              @update:model-value="pushToggle($event)"
            />
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-volume-2"
                  class="w-4 h-4 text-amber-500"
                />
              </div>
              <div>
                <p class="text-sm font-medium">
                  Sonidos de recompensa
                </p>
                <p class="text-xs text-muted">
                  Reproduce tonos épicos al completar tareas
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <UButton
                icon="i-lucide-volume-2"
                size="sm"
                color="neutral"
                variant="ghost"
                class="rounded-lg"
                @click="play('task')"
              />
              <UInput
                :model-value="soundVolume"
                type="range"
                min="0"
                max="1"
                step="0.05"
                class="w-28"
                @update:model-value="setSoundVolume(Number($event))"
              />
              <USwitch
                :model-value="soundEnabled"
                @update:model-value="setSoundEnabled"
              />
            </div>
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-globe"
                  class="w-4 h-4 text-violet-500"
                />
              </div>
              <label class="text-sm font-medium">Idioma</label>
            </div>
            <USelect
              model-value="es"
              size="sm"
              :items="[
                { label: 'Español', value: 'es' },
                { label: 'English', value: 'en' }
              ]"
              class="w-36"
            />
          </div>

          <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                <UIcon
                  name="i-lucide-clock"
                  class="w-4 h-4 text-cyan-500"
                />
              </div>
              <label class="text-sm font-medium">Zona horaria</label>
            </div>
            <USelect
              model-value="America/Mexico_City"
              size="sm"
              :items="[
                { label: 'Ciudad de México (GMT-6)', value: 'America/Mexico_City' },
                { label: 'Madrid (GMT+2)', value: 'Europe/Madrid' },
                { label: 'Buenos Aires (GMT-3)', value: 'America/Argentina/Buenos_Aires' }
              ]"
              class="w-48"
            />
          </div>
        </div>

        <div
          v-if="activeSection === 'sync'"
          class="space-y-6"
        >
          <OfflinePanel />
        </div>
      </div>
    </div>
  </div>
</template>
