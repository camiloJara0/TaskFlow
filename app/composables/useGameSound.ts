let ctx: AudioContext | null = null

function getCtx(): AudioContext | null {
  if (typeof window === 'undefined') return null
  const AC: typeof AudioContext | undefined = window.AudioContext || (window as unknown as { webkitAudioContext?: typeof AudioContext }).webkitAudioContext
  if (!AC) return null
  if (!ctx) ctx = new AC()
  if (ctx.state === 'suspended') ctx.resume()
  return ctx
}

function tone(
  c: AudioContext,
  freq: number,
  start: number,
  duration: number,
  type: OscillatorType,
  volume: number,
  slideTo?: number
) {
  const osc = c.createOscillator()
  const gain = c.createGain()
  osc.type = type
  osc.frequency.setValueAtTime(freq, c.currentTime + start)
  if (slideTo) {
    osc.frequency.exponentialRampToValueAtTime(Math.max(1, slideTo), c.currentTime + start + duration)
  }
  gain.gain.setValueAtTime(0.0001, c.currentTime + start)
  gain.gain.exponentialRampToValueAtTime(volume, c.currentTime + start + 0.02)
  gain.gain.exponentialRampToValueAtTime(0.0001, c.currentTime + start + duration)
  osc.connect(gain)
  gain.connect(c.destination)
  osc.start(c.currentTime + start)
  osc.stop(c.currentTime + start + duration + 0.05)
}

function arpeggio(notes: number[], type: OscillatorType, volume: number, spacing = 0.09, slideTo?: number) {
  const c = getCtx()
  if (!c) return
  notes.forEach((f, i) => tone(c, f, i * spacing, 0.5, type, volume, slideTo))
}

export function useGameSound() {
  const soundEnabled = useState<boolean>('gamification-sound-enabled', () => true)
  const soundVolume = useState<number>('gamification-sound-volume', () => 0.6)

  function play(name: 'task' | 'level' | 'achievement' | 'streak' | 'evolution' | 'milestone') {
    if (typeof window === 'undefined') return
    if (!soundEnabled.value) return
    const vol = Math.max(0.05, Math.min(1, soundVolume.value))
    const c = getCtx()
    if (!c) return

    switch (name) {
      case 'task':
        arpeggio([523.25, 659.25, 783.99], 'triangle', vol * 0.4, 0.06)
        break
      case 'level':
        arpeggio([523.25, 659.25, 783.99, 1046.5, 1318.5], 'sine', vol * 0.5, 0.12)
        arpeggio([1567.98, 2093], 'sine', vol * 0.3, 0.12)
        break
      case 'achievement':
        arpeggio([659.25, 830.61, 987.77, 1318.51], 'triangle', vol * 0.5, 0.1)
        break
      case 'streak':
        arpeggio([392, 523.25, 659.25, 783.99], 'triangle', vol * 0.45, 0.11)
        break
      case 'evolution':
        arpeggio([261.63, 329.63, 392, 523.25, 659.25, 783.99, 1046.5], 'sine', vol * 0.45, 0.1)
        break
      case 'milestone':
        arpeggio([523.25, 523.25, 659.25, 783.99, 1046.5], 'triangle', vol * 0.5, 0.14)
        break
    }
  }

  return { soundEnabled, soundVolume, play }
}
