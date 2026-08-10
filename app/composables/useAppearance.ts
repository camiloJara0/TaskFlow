export type FontSize = 'small' | 'medium' | 'large'

export interface AppearanceSettings {
  fontSize: FontSize
  reducedMotion: boolean
  backgroundImage: string
  solidTheme: boolean
}

const STORAGE_KEY = 'taskflow-appearance'

const DEFAULTS: AppearanceSettings = {
  fontSize: 'medium',
  reducedMotion: false,
  backgroundImage: '',
  solidTheme: false
}

function readStored(): AppearanceSettings {
  if (typeof window === 'undefined') return { ...DEFAULTS }
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return { ...DEFAULTS }
    const parsed = JSON.parse(raw)
    return {
      ...DEFAULTS,
      ...(parsed && typeof parsed === 'object' ? parsed : {})
    }
  } catch {
    return { ...DEFAULTS }
  }
}

let loaded = false

export function useAppearance() {
  const settings = useState<AppearanceSettings>('appearance-settings', () => ({ ...DEFAULTS }))

  const fontSize = computed<FontSize>({
    get: () => settings.value.fontSize,
    set: (value: FontSize | string) => {
      if (value === 'small' || value === 'medium' || value === 'large') {
        update({ fontSize: value })
      }
    }
  })

  const reducedMotion = computed<boolean>({
    get: () => settings.value.reducedMotion,
    set: (value: boolean) => update({ reducedMotion: value })
  })

  const backgroundImage = computed<string>({
    get: () => settings.value.backgroundImage,
    set: (value: string) => update({ backgroundImage: value })
  })

  const solidTheme = computed<boolean>({
    get: () => settings.value.solidTheme,
    set: (value: boolean) => update({ solidTheme: value })
  })

  function update(patch: Partial<AppearanceSettings>) {
    settings.value = { ...settings.value, ...patch }
    persist()
    apply()
  }

  function persist() {
    if (typeof window === 'undefined') return
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(settings.value))
      // const { updateAppearance } = useProfileService()
      // await updateAppearance(settings.value)
    } catch {
      // almacenamiento no disponible
    }
  }

  function apply() {
    if (typeof window === 'undefined') return
    const root = document.documentElement
    root.dataset.fontSize = settings.value.fontSize
    root.dataset.solidTheme = String(settings.value.solidTheme)
    root.classList.toggle('reduce-motion', settings.value.reducedMotion)
  }

  onMounted(() => {
    if (!loaded) {
      loaded = true
      settings.value = readStored()
    }
    apply()
  })

  return {
    settings,
    fontSize,
    reducedMotion,
    backgroundImage,
    solidTheme,
    update,
    apply
  }
}
