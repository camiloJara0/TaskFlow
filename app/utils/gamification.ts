export type Rarity = 'comun' | 'raro' | 'epico' | 'legendario'

export type AchievementCategory = 'productividad' | 'disciplina' | 'constancia' | 'estudio' | 'trabajo' | 'organizacion'

export interface AchievementDef {
  id: string
  title: string
  description: string
  icon: string
  rarity: Rarity
  category: AchievementCategory
  hint?: string
}

export interface GamificationState {
  xp: number
  level: number
  streak: number
  bestStreak: number
  lastActiveDay: string | null
  totalCompleted: number
  dailyHistory: Record<string, number>
  xpHistory: Record<string, number>
  achievements: Record<string, string>
  completedTaskIds: number[]
  soundEnabled: boolean
  soundVolume: number
}

export const GAMIFICATION_VERSION = 1

/* ---------- Niveles y títulos ---------- */

export interface LevelTitle {
  level: number
  title: string
  icon: string
}

export const LEVEL_TITLES: LevelTitle[] = [
  { level: 100, title: 'Dios Supremo', icon: 'i-lucide-crown' },
  { level: 75, title: 'Titán', icon: 'i-lucide-landmark' },
  { level: 50, title: 'Dios Ascendente', icon: 'i-lucide-star' },
  { level: 35, title: 'Héroe Olímpico', icon: 'i-lucide-shield' },
  { level: 20, title: 'Semidiós', icon: 'i-lucide-angel' },
  { level: 10, title: 'Guerrero', icon: 'i-lucide-swords' },
  { level: 5, title: 'Discípulo', icon: 'i-lucide-book-open' },
  { level: 1, title: 'Mortal', icon: 'i-lucide-user' }
]

export function levelTitle(level: number): LevelTitle {
  return LEVEL_TITLES.find(t => level >= t.level) || LEVEL_TITLES[LEVEL_TITLES.length - 1]!
}

export function xpForLevel(level: number): number {
  return Math.round(80 * Math.pow(level, 1.5))
}

export function xpForNextLevel(level: number): number {
  return xpForLevel(level + 1)
}

/* ---------- XP por prioridad ---------- */

export const XP_BY_PRIORITY: Record<string, number> = {
  urgente: 100,
  urgent: 100,
  critica: 100,
  alta: 50,
  high: 50,
  media: 25,
  medium: 25,
  baja: 10,
  low: 10
}

export function xpForTask(prioridad: unknown): number {
  const p = String(prioridad ?? '').trim().toLowerCase()
  return XP_BY_PRIORITY[p] ?? 25
}

/* ---------- Racha ---------- */

export const STREAK_MILESTONES = [7, 15, 30, 60, 100, 365]

export function streakMilestone(days: number): number | null {
  return STREAK_MILESTONES.find(m => days === m) ?? null
}

export function streakLabel(days: number): string {
  return days >= 365
    ? 'Año de leyenda'
    : days >= 100
      ? 'Racha legendaria'
      : days >= 60
        ? 'Racha épica'
        : days >= 30
          ? 'Racha imparable'
          : days >= 15
            ? 'Racha sobresaliente'
            : days >= 7
              ? 'Racha sólida'
              : days >= 3
                ? 'Racha activa'
                : 'Racha incipiente'
}

/* ---------- Logros ---------- */

export const ACHIEVEMENTS: AchievementDef[] = [
  {
    id: 'primera-victoria',
    title: 'Primera Victoria',
    description: 'Completa tu primera tarea.',
    icon: 'i-lucide-trophy',
    rarity: 'comun',
    category: 'productividad',
    hint: 'Completa cualquier tarea'
  },
  {
    id: 'productivo',
    title: 'Productivo',
    description: 'Completa 25 tareas.',
    icon: 'i-lucide-zap',
    rarity: 'raro',
    category: 'productividad',
    hint: '25 tareas completadas'
  },
  {
    id: 'incansable',
    title: 'Incansable',
    description: 'Completa 100 tareas.',
    icon: 'i-lucide-flame',
    rarity: 'epico',
    category: 'productividad',
    hint: '100 tareas completadas'
  },
  {
    id: 'maestro-organizacion',
    title: 'Maestro de la Organización',
    description: 'Completa 500 tareas.',
    icon: 'i-lucide-award',
    rarity: 'legendario',
    category: 'organizacion',
    hint: '500 tareas completadas'
  },
  {
    id: 'racha-semana',
    title: 'Constancia Semanal',
    description: 'Mantén una racha de 7 días.',
    icon: 'i-lucide-calendar-check',
    rarity: 'comun',
    category: 'constancia',
    hint: 'Racha de 7 días'
  },
  {
    id: 'racha-quince',
    title: 'Disciplina',
    description: 'Mantén una racha de 15 días.',
    icon: 'i-lucide-calendar-check-2',
    rarity: 'raro',
    category: 'constancia',
    hint: 'Racha de 15 días'
  },
  {
    id: 'imparable',
    title: 'Imparable',
    description: 'Mantén una racha de 30 días.',
    icon: 'i-lucide-flame',
    rarity: 'epico',
    category: 'constancia',
    hint: 'Racha de 30 días'
  },
  {
    id: 'leyenda',
    title: 'Leyenda',
    description: 'Mantén una racha de 100 días.',
    icon: 'i-lucide-gem',
    rarity: 'legendario',
    category: 'constancia',
    hint: 'Racha de 100 días'
  },
  {
    id: 'madrugador',
    title: 'Madrugador',
    description: 'Completa una tarea antes de las 6:00 AM.',
    icon: 'i-lucide-sunrise',
    rarity: 'raro',
    category: 'disciplina',
    hint: 'Completa una tarea muy temprano'
  },
  {
    id: 'nocturno',
    title: 'Nocturno',
    description: 'Completa una tarea después de las 11:00 PM.',
    icon: 'i-lucide-moon-star',
    rarity: 'raro',
    category: 'disciplina',
    hint: 'Completa una tarea de noche'
  },
  {
    id: 'perfeccionista',
    title: 'Perfeccionista',
    description: 'Completa todas las tareas programadas de una semana.',
    icon: 'i-lucide-check-check',
    rarity: 'epico',
    category: 'organizacion',
    hint: 'Completa 20 tareas en una semana'
  },
  {
    id: 'rapido',
    title: 'Velocidad del Viento',
    description: 'Completa 10 tareas en un solo día.',
    icon: 'i-lucide-wind',
    rarity: 'epico',
    category: 'productividad',
    hint: '10 tareas en un día'
  },
  {
    id: 'explorador',
    title: 'Explorador de Espacios',
    description: 'Crea o participa en 3 espacios de trabajo.',
    icon: 'i-lucide-folder-kanban',
    rarity: 'raro',
    category: 'trabajo',
    hint: 'Usa varios espacios'
  },
  {
    id: 'trabajo-equipo',
    title: 'Trabajo en Equipo',
    description: 'Completa tareas con subtareas o en equipo.',
    icon: 'i-lucide-users',
    rarity: 'raro',
    category: 'trabajo',
    hint: 'Colabora en tus tareas'
  },
  {
    id: 'nivel-10',
    title: 'Camino del Héroe',
    description: 'Alcanza el nivel 10.',
    icon: 'i-lucide-swords',
    rarity: 'raro',
    category: 'estudio',
    hint: 'Llega al nivel 10'
  },
  {
    id: 'nivel-20',
    title: 'Ascenso',
    description: 'Alcanza el nivel 20.',
    icon: 'i-lucide-angel',
    rarity: 'epico',
    category: 'estudio',
    hint: 'Llega al nivel 20'
  },
  {
    id: 'nivel-35',
    title: 'Héroe Olímpico',
    description: 'Alcanza el nivel 35.',
    icon: 'i-lucide-shield',
    rarity: 'epico',
    category: 'estudio',
    hint: 'Llega al nivel 35'
  },
  {
    id: 'nivel-50',
    title: 'Divinidad',
    description: 'Alcanza el nivel 50.',
    icon: 'i-lucide-star',
    rarity: 'legendario',
    category: 'estudio',
    hint: 'Llega al nivel 50'
  }
]

export const RARITY_META: Record<Rarity, { label: string, text: string, bg: string, ring: string, glow: string }> = {
  comun: {
    label: 'Común',
    text: 'text-slate-500 dark:text-slate-400',
    bg: 'bg-slate-100 dark:bg-slate-500/15',
    ring: 'ring-slate-300 dark:ring-slate-500/40',
    glow: 'shadow-slate-400/20'
  },
  raro: {
    label: 'Raro',
    text: 'text-violet-500 dark:text-violet-400',
    bg: 'bg-violet-100 dark:bg-violet-500/15',
    ring: 'ring-violet-300 dark:ring-violet-500/40',
    glow: 'shadow-violet-400/30'
  },
  epico: {
    label: 'Épico',
    text: 'text-purple-600 dark:text-purple-400',
    bg: 'bg-purple-100 dark:bg-purple-500/15',
    ring: 'ring-purple-400 dark:ring-purple-500/40',
    glow: 'shadow-purple-500/40'
  },
  legendario: {
    label: 'Legendario',
    text: 'text-amber-600 dark:text-amber-400',
    bg: 'bg-amber-100 dark:bg-amber-500/15',
    ring: 'ring-amber-400 dark:ring-amber-400/50',
    glow: 'shadow-amber-500/50'
  }
}

export const ACHIEVEMENT_CATEGORIES: { id: AchievementCategory, label: string, icon: string }[] = [
  { id: 'productividad', label: 'Productividad', icon: 'i-lucide-zap' },
  { id: 'disciplina', label: 'Disciplina', icon: 'i-lucide-sun' },
  { id: 'constancia', label: 'Constancia', icon: 'i-lucide-calendar-check' },
  { id: 'estudio', label: 'Estudio', icon: 'i-lucide-book-open' },
  { id: 'trabajo', label: 'Trabajo', icon: 'i-lucide-briefcase' },
  { id: 'organizacion', label: 'Organización', icon: 'i-lucide-layout-grid' }
]

/* ---------- Mascota ---------- */

export interface PetPhase {
  level: number
  name: string
  description: string
}

export const PET_PHASES: PetPhase[] = [
  { level: 1, name: 'Chispa Celestial', description: 'Una pequeña criatura recién nacida.' },
  { level: 5, name: 'Aurelio de Bronce', description: 'Aparecen detalles dorados en su cuerpo.' },
  { level: 10, name: 'Aurelio Alado', description: 'Crecen alas doradas.' },
  { level: 20, name: 'Guardián Olímpico', description: 'Porta una armadura divina.' },
  { level: 35, name: 'Espíritu Radiante', description: 'Un aura luminosa lo rodea.' },
  { level: 50, name: 'Heraldo Semidivino', description: 'Su forma alcanza la semidivinidad.' },
  { level: 75, name: 'Deidad Suprema', description: 'Su forma olímpica es suprema.' }
]

export function petPhaseFor(level: number): PetPhase {
  let phase = PET_PHASES[0]!
  for (const p of PET_PHASES) {
    if (level >= p.level) phase = p
  }
  return phase
}
/* ---------- Métricas ---------- */

export interface GamificationMetrics {
  level: number
  streak: number
  totalCompleted: number
  completedToday: number
  completedWeek: number
  completedMonth: number
  xp: number
  xpThisWeek: number
}

export function sumRange(history: Record<string, number>, days: number): number {
  const now = new Date()
  let sum = 0
  for (let i = 0; i < days; i++) {
    const d = new Date(now)
    d.setDate(now.getDate() - i)
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
    sum += history[key] || 0
  }
  return sum
}

export function productivityScore(metrics: GamificationMetrics): number {
  const daysWeight = metrics.streak * 2
  const tasksWeight = metrics.completedToday * 5
  return Math.min(100, Math.round(daysWeight + tasksWeight))
}

export function productivityLabel(score: number): string {
  if (score >= 90) return 'Legendaria'
  if (score >= 70) return 'Épica'
  if (score >= 50) return 'Alta'
  if (score >= 30) return 'Constante'
  return 'En desarrollo'
}

export function productivityColor(score: number): string {
  if (score >= 90) return 'from-amber-400 to-orange-400 shadow-amber-500/40'
  if (score >= 70) return 'from-purple-400 to-violet-500 shadow-purple-500/40'
  if (score >= 50) return 'from-blue-400 to-cyan-400 shadow-blue-500/40'
  if (score >= 30) return 'from-emerald-400 to-teal-400 shadow-emerald-500/40'
  return 'from-slate-300 to-slate-400 shadow-slate-400/40'
}

export function xpProgress(state: Pick<GamificationState, 'xp' | 'level'>): { current: number, needed: number, percent: number } {
  const needed = xpForNextLevel(state.level)
  const percent = Math.min(100, Math.round((state.xp / needed) * 100))
  return { current: state.xp, needed, percent }
}
