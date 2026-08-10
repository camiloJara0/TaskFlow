import type { GamificationState, AchievementDef } from '~/utils/gamification'
import { GAMIFICATION_VERSION, ACHIEVEMENTS, xpForTask, xpProgress, streakMilestone, levelTitle, petPhaseFor, productivityScore, productivityLabel, productivityColor, sumRange } from '~/utils/gamification'
import type { GamificationProfile } from '~/types/api'

const STORAGE_KEY = 'taskflow-gamification'

export interface XpEvent {
  id: number
  amount: number
  title: string
}

export interface LevelUpEvent {
  level: number
  title: string
  icon: string
  isNew: boolean
}

export interface AchievementEvent {
  achievement: AchievementDef
  isNew: boolean
}

export interface StreakEvent {
  days: number
  isNew: boolean
}

function defaultState(): GamificationState {
  return {
    xp: 0,
    level: 1,
    streak: 0,
    bestStreak: 0,
    lastActiveDay: null,
    totalCompleted: 0,
    dailyHistory: {},
    xpHistory: {},
    achievements: {},
    completedTaskIds: [],
    soundEnabled: true,
    soundVolume: 0.6
  }
}

function todayKey(date: Date = new Date()): string {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function daysBetween(a: string, b: string): number {
  const ay = Number(a.split('-')[0])
  const am = Number(a.split('-')[1])
  const ad = Number(a.split('-')[2])
  const by = Number(b.split('-')[0])
  const bm = Number(b.split('-')[1])
  const bd = Number(b.split('-')[2])
  const ta = new Date(ay, am - 1, ad).getTime()
  const tb = new Date(by, bm - 1, bd).getTime()
  return Math.round((tb - ta) / 86400000)
}

function profileToState(profile: GamificationProfile): GamificationState {
  const dailyHistory: Record<string, number> = {}
  const xpHistory: Record<string, number> = {}
  for (const entry of profile.diario ?? []) {
    dailyHistory[entry.fecha] = entry.tareas
    xpHistory[entry.fecha] = entry.xp
  }
  const achievements: Record<string, string> = {}
  for (const logro of profile.logros ?? []) {
    achievements[logro.slug] = logro.desbloqueado_en
  }
  return {
    xp: profile.xp ?? 0,
    level: profile.nivel ?? 1,
    streak: profile.racha ?? 0,
    bestStreak: profile.mejor_racha ?? 0,
    lastActiveDay: profile.ultimo_dia_activo ?? null,
    totalCompleted: profile.tareas_completadas ?? 0,
    dailyHistory,
    xpHistory,
    achievements,
    completedTaskIds: profile.tareas_completadas_ids ?? [],
    soundEnabled: profile.sonidos_activados ?? true,
    soundVolume: profile.volumen ?? 0.6
  }
}

let persistenceInit = false

export function useGamification() {
  const { soundEnabled, soundVolume, play } = useGameSound()
  const gamification = useGamificationService()

  const state = useState<GamificationState>('gamification-state', defaultState)

  const xpEvents = useState<XpEvent[]>('gamification-xp-events', () => [])
  const levelUpEvent = useState<LevelUpEvent | null>('gamification-level-up', () => null)
  const achievementQueue = useState<AchievementEvent[]>('gamification-achievement-queue', () => [])
  const streakEvent = useState<StreakEvent | null>('gamification-streak', () => null)
  const confettiBurst = useState<number>('gamification-confetti', () => 0)
  const petReaction = useState<'idle' | 'happy' | 'thrilled'>('gamification-pet-reaction', () => 'idle')
  const petPulse = useState<number>('gamification-pet-pulse', () => 0)

  const xpInfo = computed(() => xpProgress(state.value))
  const title = computed(() => levelTitle(state.value.level))
  const nextTitle = computed(() => levelTitle(state.value.level + 1))
  const phase = computed(() => petPhaseFor(state.value.level))

  const todayCount = computed(() => state.value.dailyHistory[todayKey()] || 0)
  const weekCount = computed(() => sumRange(state.value.dailyHistory, 7))
  const monthCount = computed(() => sumRange(state.value.dailyHistory, 30))
  const xpThisWeek = computed(() => sumRange(state.value.xpHistory, 7))

  const achievementsUnlocked = computed(() => {
    return ACHIEVEMENTS.filter(a => state.value.achievements[a.id])
  })
  const lockedAchievements = computed(() => ACHIEVEMENTS.filter(a => !state.value.achievements[a.id]))
  const productivity = computed(() => {
    const score = productivityScore({
      level: state.value.level,
      streak: state.value.streak,
      totalCompleted: state.value.totalCompleted,
      completedToday: todayCount.value,
      completedWeek: weekCount.value,
      completedMonth: monthCount.value,
      xp: state.value.xp,
      xpThisWeek: xpThisWeek.value
    })
    return { score, label: productivityLabel(score), color: productivityColor(score) }
  })

  function persist() {
    if (typeof window === 'undefined') return
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ version: GAMIFICATION_VERSION, state: state.value }))
    } catch {
      // almacenamiento no disponible
    }
  }

  function applyProfile(profile: GamificationProfile) {
    state.value = profileToState(profile)
    soundEnabled.value = state.value.soundEnabled
    soundVolume.value = state.value.soundVolume
    persist()
  }

  async function load() {
    if (typeof window === 'undefined') return
    if (!persistenceInit) {
      persistenceInit = true
      try {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (raw) {
          const parsed = JSON.parse(raw)
          if (parsed?.version === GAMIFICATION_VERSION) {
            state.value = { ...defaultState(), ...parsed.state }
          }
        }
      } catch {
        // datos corruptos: se ignora
      }
    }

    try {
      const { data } = await gamification.getProfile()
      applyProfile(data)
    } catch {
      // sin backend disponible: se mantiene el estado local
    }
  }

  async function reset() {
    state.value = defaultState()
    xpEvents.value = []
    levelUpEvent.value = null
    achievementQueue.value = []
    streakEvent.value = null
    persist()
    try {
      await gamification.reset()
    } catch {
      // sin backend disponible
    }
  }

  /* ---------- Lógica de completado ---------- */

  interface LocalOutcome {
    levelUp: boolean
    newLevel: number
    milestone: number | null
    unlocked: AchievementDef[]
  }

  function collectEligibleIds(s: GamificationState, hour: number): string[] {
    const ids: string[] = []
    if (s.streak >= 7) ids.push('racha-semana')
    if (s.streak >= 15) ids.push('racha-quince')
    if (s.streak >= 30) ids.push('imparable')
    if (s.streak >= 100) ids.push('leyenda')
    if (s.totalCompleted >= 1) ids.push('primera-victoria')
    if (s.totalCompleted >= 25) ids.push('productivo')
    if (s.totalCompleted >= 100) ids.push('incansable')
    if (s.totalCompleted >= 500) ids.push('maestro-organizacion')
    if ((s.dailyHistory[todayKey()] || 0) >= 10) ids.push('rapido')
    if (sumRange(s.dailyHistory, 7) >= 20) ids.push('perfeccionista')
    if (hour < 6) ids.push('madrugador')
    if (hour >= 23) ids.push('nocturno')
    if (s.level >= 10) ids.push('nivel-10')
    if (s.level >= 20) ids.push('nivel-20')
    if (s.level >= 35) ids.push('nivel-35')
    if (s.level >= 50) ids.push('nivel-50')
    return ids
  }

  function applyLocalCompletion(taskId: number, amount: number, day: string, hour: number): LocalOutcome {
    const s = state.value
    const alreadyDone = taskId > 0 && s.completedTaskIds.includes(taskId)

    if (!alreadyDone && taskId > 0) {
      s.completedTaskIds.push(taskId)
    }

    /* racha */
    if (s.lastActiveDay === null) {
      s.streak = 1
      s.lastActiveDay = day
    } else if (s.lastActiveDay !== day) {
      const diff = daysBetween(s.lastActiveDay, day)
      s.streak = diff <= 1 ? s.streak + 1 : 1
      s.lastActiveDay = day
    }
    if (s.streak > s.bestStreak) {
      s.bestStreak = s.streak
    }

    /* contadores por día */
    s.dailyHistory[day] = (s.dailyHistory[day] || 0) + 1
    s.xpHistory[day] = (s.xpHistory[day] || 0) + amount
    s.totalCompleted += 1

    /* XP y subida de nivel */
    const prevLevel = s.level
    s.xp += amount
    let newLevel = s.level
    let guard = 0
    while (newLevel < 100 && s.xp >= xpProgress({ xp: s.xp, level: newLevel }).needed && guard < 200) {
      s.xp -= xpProgress({ xp: s.xp, level: newLevel }).needed
      newLevel += 1
      guard += 1
    }
    s.level = newLevel

    /* logros (cálculo local) */
    const unlocked: AchievementDef[] = []
    for (const id of collectEligibleIds(s, hour)) {
      const def = ACHIEVEMENTS.find(a => a.id === id)
      if (!def || s.achievements[id]) continue
      s.achievements[id] = new Date().toISOString()
      unlocked.push(def)
    }

    return {
      levelUp: newLevel > prevLevel,
      newLevel,
      milestone: streakMilestone(s.streak),
      unlocked
    }
  }

  function triggerEvents(params: {
    amount: number
    title?: string
    levelUp: boolean
    newLevel: number
    milestone: number | null
    achievements: AchievementDef[]
  }) {
    play('task')
    pushXpEvent(params.amount, params.title)

    if (params.levelUp) {
      levelUpEvent.value = {
        level: params.newLevel,
        title: levelTitle(params.newLevel).title,
        icon: levelTitle(params.newLevel).icon,
        isNew: true
      }
      confettiBurst.value += 1
      petReaction.value = 'thrilled'
      petPulse.value += 1
      play('level')
      setTimeout(() => {
        petReaction.value = state.value.streak >= 3 ? 'happy' : 'idle'
      }, 3500)
    } else if (params.milestone) {
      streakEvent.value = { days: params.milestone, isNew: true }
      confettiBurst.value += 1
      petReaction.value = 'thrilled'
      petPulse.value += 1
      play('milestone')
      setTimeout(() => {
        petReaction.value = 'happy'
      }, 3500)
    } else {
      petReaction.value = 'happy'
      petPulse.value += 1
      setTimeout(() => {
        petReaction.value = 'idle'
      }, 2500)
    }

    for (const achievement of params.achievements) {
      achievementQueue.value.push({ achievement, isNew: true })
      play('achievement')
      confettiBurst.value += 1
    }
  }

  function registerTaskCompletion(task: {
    id?: number
    titulo?: string
    prioridad?: string
  }) {
    const now = new Date()
    const day = todayKey(now)
    const hour = now.getHours()
    const amount = xpForTask(task?.prioridad)
    const taskId = task?.id ?? 0
    const title = task?.titulo

    if (!taskId) {
      const outcome = applyLocalCompletion(taskId, amount, day, hour)
      persist()
      triggerEvents({
        amount, title,
        levelUp: outcome.levelUp, newLevel: outcome.newLevel,
        milestone: outcome.milestone, achievements: outcome.unlocked
      })
      return
    }

    gamification.registerTaskCompletion(taskId)
      .then(({ data }) => {
        applyProfile(data.perfil)
        const serverAchievements: AchievementDef[] = (data.logros_nuevos ?? [])
          .map(logro => ACHIEVEMENTS.find(a => a.id === logro.slug))
          .filter((achievement): achievement is AchievementDef => Boolean(achievement))
        triggerEvents({
          amount: data.xp_ganado ?? amount,
          title,
          levelUp: data.subio_nivel === true,
          newLevel: data.nuevo_nivel ?? data.perfil.nivel,
          milestone: data.hito_racha ?? null,
          achievements: serverAchievements
        })
      })
      .catch(() => {
        const outcome = applyLocalCompletion(taskId, amount, day, hour)
        persist()
        triggerEvents({
          amount, title,
          levelUp: outcome.levelUp, newLevel: outcome.newLevel,
          milestone: outcome.milestone, achievements: outcome.unlocked
        })
      })
  }

  function pushXpEvent(amount: number, title?: string) {
    const evt: XpEvent = { id: Date.now() + Math.random(), amount, title: title || 'Tarea completada' }
    xpEvents.value.push(evt)
    setTimeout(() => {
      xpEvents.value = xpEvents.value.filter(e => e.id !== evt.id)
    }, 2600)
  }

  function dismissLevelUp() {
    levelUpEvent.value = null
  }

  function dismissNextAchievement() {
    achievementQueue.value.shift()
  }

  function dismissStreak() {
    streakEvent.value = null
  }

  /* ---------- Preferencias de sonido ---------- */

  function setSoundEnabled(value: boolean) {
    soundEnabled.value = value
    state.value.soundEnabled = value
    persist()
    gamification.updateSound(value, state.value.soundVolume).catch(() => {})
  }

  function setSoundVolume(value: number) {
    soundVolume.value = value
    state.value.soundVolume = value
    persist()
    gamification.updateSound(state.value.soundEnabled, value).catch(() => {})
  }

  onMounted(() => {
    load()
    soundEnabled.value = state.value.soundEnabled
    soundVolume.value = state.value.soundVolume
  })

  return {
    state, xpInfo, title, nextTitle, phase,
    todayCount, weekCount, monthCount, xpThisWeek,
    achievementsUnlocked, lockedAchievements,
    productivity, xpEvents, levelUpEvent, achievementQueue, streakEvent,
    confettiBurst, petReaction, petPulse,
    registerTaskCompletion, reset,
    dismissLevelUp, dismissNextAchievement, dismissStreak,
    soundEnabled, soundVolume, setSoundEnabled, setSoundVolume, play
  }
}
