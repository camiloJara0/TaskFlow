<?php

namespace App\Http\Controllers;

use App\Models\EspacioTrabajo;
use App\Models\Gamificacion;
use App\Models\Logro;
use App\Models\MiembroEquipo;
use App\Models\Notificacion;
use App\Models\Tarea;
use App\Models\User;
use App\Models\XpEvento;
use App\Services\NotificacionService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GamificacionController extends Controller
{
    private const XP_POR_PRIORIDAD = [
        'urgente' => 100, 'urgent' => 100, 'critica' => 100,
        'alta' => 50, 'high' => 50,
        'media' => 25, 'medium' => 25,
        'baja' => 10, 'low' => 10,
    ];

    private const HITOS_RACHA = [7, 15, 30, 60, 100, 365];

    private const XP_HITO_RACHA = 50;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->perfilUsuario(Auth::id()),
        ]);
    }

    public function logros()
    {
        $gamificacion = $this->obtenerOCrear(Auth::id());

        $desbloqueados = DB::table('gamificacion_logros')
            ->where('gamificacion_id', $gamificacion->id)
            ->pluck('desbloqueado_en', 'logro_id');

        $logros = Logro::orderBy('meta')
            ->orderBy('id')
            ->get()
            ->map(function (Logro $logro) use ($desbloqueados) {
                $logro->desbloqueado = $desbloqueados->has($logro->id);
                $logro->desbloqueado_en = $desbloqueados->get($logro->id)
                    ? \Carbon\Carbon::parse($desbloqueados->get($logro->id))->toIso8601String()
                    : null;
                return $logro;
            });

        return response()->json(['success' => true, 'data' => $logros]);
    }

    public function tareasCompletadas(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => 'required|integer',
        ]);

        $tarea = Tarea::find($validated['tarea_id']);
        if (!$tarea) {
            return response()->json(['success' => false, 'message' => 'Tarea no encontrada'], 404);
        }
        if (!$this->tareaPerteneceAlUsuario($tarea)) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a esta tarea'], 403);
        }

        $gamificacion = $this->obtenerOCrear(Auth::id());

        if ($this->yaRegistrada($gamificacion, $tarea->id)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'xp_ganado' => 0,
                    'subio_nivel' => false,
                    'nuevo_nivel' => null,
                    'hito_racha' => null,
                    'logros_nuevos' => [],
                    'perfil' => $this->perfilUsuario(Auth::id()),
                ],
            ]);
        }

        $xp = $this->xpParaPrioridad($tarea->prioridad);
        $hora = now()->hour;
        $esMadrugada = $hora < 6;
        $esNoche = $hora >= 23;

        DB::beginTransaction();
        try {
            $hitoRacha = $this->actualizarRacha($gamificacion);

            $gamificacion->xp += $xp;
            $subioNivel = $this->aplicarNiveles($gamificacion);
            $gamificacion->tareas_completadas++;
            $gamificacion->save();

            XpEvento::create([
                'gamificacion_id' => $gamificacion->id,
                'tarea_id' => $tarea->id,
                'cantidad' => $xp,
                'motivo' => 'tarea_completada',
            ]);

            $diario = $gamificacion->registrosDiarios()->firstOrCreate(['fecha' => now()->toDateString()]);
            $diario->increment('tareas');
            $diario->increment('xp', $xp);

            $nuevosLogros = $this->evaluarLogros($gamificacion, $esMadrugada, $esNoche);

            if ($hitoRacha && !$this->tieneLogroMeta($nuevosLogros, $hitoRacha)) {
                XpEvento::create([
                    'gamificacion_id' => $gamificacion->id,
                    'tarea_id' => null,
                    'cantidad' => self::XP_HITO_RACHA,
                    'motivo' => 'hito_racha',
                ]);
                $this->notificarHitoRacha($gamificacion, $hitoRacha);
            }

            foreach ($nuevosLogros as $logro) {
                $this->notificarLogro($gamificacion, $logro);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'xp_ganado' => $xp,
                    'subio_nivel' => $subioNivel,
                    'nuevo_nivel' => $subioNivel ? $gamificacion->nivel : null,
                    'hito_racha' => $hitoRacha,
                    'logros_nuevos' => array_map(fn (Logro $l) => [
                        'slug' => $l->slug,
                        'titulo' => $l->titulo,
                        'icono' => $l->icono,
                        'rareza' => $l->rareza,
                    ], $nuevosLogros),
                    'perfil' => $this->perfilUsuario(Auth::id()),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar tarea: ' . $e->getMessage()], 500);
        }
    }

    public function sonidos(Request $request)
    {
        $validated = $request->validate([
            'sonidos_activados' => 'sometimes|boolean',
            'volumen' => 'sometimes|numeric|min:0|max:1',
        ]);

        $gamificacion = $this->obtenerOCrear(Auth::id());
        $preferencias = $gamificacion->preferencias ?? [];
        $gamificacion->preferencias = array_merge($preferencias, $validated);
        $gamificacion->save();

        return response()->json(['success' => true, 'data' => null]);
    }

    public function destroy()
    {
        $gamificacion = Gamificacion::where('usuario_id', Auth::id())->first();
        if ($gamificacion) {
            $gamificacion->delete();
        }
        return response()->json(['success' => true, 'message' => 'Progreso de gamificación reiniciado']);
    }

    private function perfilUsuario(int $usuarioId): array
    {
        $gamificacion = $this->obtenerOCrear($usuarioId);
        $hoy = now()->toDateString();

        $diario = $gamificacion->registrosDiarios()
            ->whereBetween('fecha', [now()->subDays(30)->toDateString(), $hoy])
            ->orderBy('fecha', 'desc')
            ->limit(30)
            ->get(['fecha', 'tareas', 'xp'])
            ->map(fn ($d) => [
                'fecha' => $d->fecha->format('Y-m-d'),
                'tareas' => $d->tareas,
                'xp' => $d->xp,
            ])
            ->values();

        $tareaIds = $gamificacion->xpEventos()
            ->where('motivo', 'tarea_completada')
            ->pluck('tarea_id')
            ->values();

        $logros = DB::table('gamificacion_logros')
            ->join('logros', 'logros.id', '=', 'gamificacion_logros.logro_id')
            ->where('gamificacion_id', $gamificacion->id)
            ->get(['logros.slug', 'gamificacion_logros.desbloqueado_en'])
            ->map(fn ($l) => [
                'slug' => $l->slug,
                'desbloqueado_en' => $l->desbloqueado_en
                    ? \Carbon\Carbon::parse($l->desbloqueado_en)->toIso8601String()
                    : null,
            ])
            ->values();

        $preferencias = $gamificacion->preferencias ?? [];

        return [
            'xp' => $gamificacion->xp,
            'nivel' => $gamificacion->nivel,
            'racha' => $gamificacion->racha,
            'mejor_racha' => $gamificacion->mejor_racha,
            'ultimo_dia_activo' => $gamificacion->ultimo_dia_activo
                ? $gamificacion->ultimo_dia_activo->format('Y-m-d')
                : null,
            'tareas_completadas' => $gamificacion->tareas_completadas,
            'tareas_completadas_ids' => $tareaIds,
            'sonidos_activados' => $preferencias['sonidos_activados'] ?? true,
            'volumen' => (float) ($preferencias['volumen'] ?? 0.6),
            'diario' => $diario,
            'logros' => $logros,
        ];
    }

    private function obtenerOCrear(int $usuarioId): Gamificacion
    {
        return Gamificacion::firstOrCreate(
            ['usuario_id' => $usuarioId],
            [
                'xp' => 0,
                'nivel' => 1,
                'racha' => 0,
                'mejor_racha' => 0,
                'tareas_completadas' => 0,
            ]
        );
    }

    private function yaRegistrada(Gamificacion $gamificacion, int $tareaId): bool
    {
        return $gamificacion->xpEventos()
            ->where('tarea_id', $tareaId)
            ->exists();
    }

    private function xpParaPrioridad(?string $prioridad): int
    {
        $p = strtolower(trim((string) $prioridad));
        return self::XP_POR_PRIORIDAD[$p] ?? 25;
    }

    private function xpParaNivel(int $nivel): int
    {
        return (int) round(80 * pow($nivel, 1.5));
    }

    private function aplicarNiveles(Gamificacion $gamificacion): bool
    {
        $subioNivel = false;
        while ($gamificacion->xp >= $this->xpParaNivel($gamificacion->nivel) && $gamificacion->nivel < 100) {
            $gamificacion->xp -= $this->xpParaNivel($gamificacion->nivel);
            $gamificacion->nivel++;
            $subioNivel = true;
        }
        return $subioNivel;
    }

    private function actualizarRacha(Gamificacion $gamificacion): ?int
    {
        $hoy = now()->toDateString();
        $ultimo = $gamificacion->ultimo_dia_activo;

        if ($ultimo === null) {
            $gamificacion->racha = 1;
        } elseif ($ultimo->format('Y-m-d') === $hoy) {
            // ya activo hoy, no cambia
        } elseif ($ultimo->startOfDay()->diffInDays(now()->startOfDay()) <= 1) {
            $gamificacion->racha++;
        } else {
            $gamificacion->racha = 1;
        }

        $gamificacion->ultimo_dia_activo = $hoy;
        $gamificacion->mejor_racha = max($gamificacion->mejor_racha, $gamificacion->racha);

        return in_array($gamificacion->racha, self::HITOS_RACHA, true) ? $gamificacion->racha : null;
    }

    private function evaluarLogros(Gamificacion $gamificacion, bool $esMadrugada, bool $esNoche): array
    {
        $hoy = now()->toDateString();
        $dias = (int) $gamificacion->registrosDiarios()
            ->whereBetween('fecha', [now()->subDays(6)->toDateString(), $hoy])
            ->sum('tareas');
        $hoyTareas = (int) ($gamificacion->registrosDiarios()
            ->where('fecha', $hoy)
            ->value('tareas') ?? 0);

        $condiciones = [
            'primera-victoria' => $gamificacion->tareas_completadas >= 1,
            'productivo' => $gamificacion->tareas_completadas >= 25,
            'incansable' => $gamificacion->tareas_completadas >= 100,
            'maestro-organizacion' => $gamificacion->tareas_completadas >= 500,
            'racha-semana' => $gamificacion->racha >= 7,
            'racha-quince' => $gamificacion->racha >= 15,
            'imparable' => $gamificacion->racha >= 30,
            'leyenda' => $gamificacion->racha >= 100,
            'rapido' => $hoyTareas >= 10,
            'perfeccionista' => $dias >= 20,
            'madrugador' => $esMadrugada,
            'nocturno' => $esNoche,
            'nivel-10' => $gamificacion->nivel >= 10,
            'nivel-20' => $gamificacion->nivel >= 20,
            'nivel-35' => $gamificacion->nivel >= 35,
            'nivel-50' => $gamificacion->nivel >= 50,
        ];

        $nuevos = [];
        foreach ($condiciones as $slug => $cumple) {
            if (!$cumple) {
                continue;
            }
            $logro = Logro::where('slug', $slug)->first();
            if (!$logro) {
                continue;
            }
            if ($gamificacion->logros()->where('logro_id', $logro->id)->exists()) {
                continue;
            }
            $gamificacion->logros()->attach($logro->id, ['desbloqueado_en' => now()]);
            $nuevos[] = $logro;
        }
        return $nuevos;
    }

    private function tieneLogroMeta(array $logros, int $meta): bool
    {
        foreach ($logros as $logro) {
            if ((int) $logro->meta === $meta) {
                return true;
            }
        }
        return false;
    }

    private function notificarLogro(Gamificacion $gamificacion, Logro $logro): void
    {
        Notificacion::create([
            'usuario_id' => $gamificacion->usuario_id,
            'titulo' => 'Logro desbloqueado: ' . $logro->titulo,
            'mensaje' => $logro->descripcion,
            'tipo' => 'logro',
            'url' => NotificacionService::urlFrontend('progress'),
        ]);

        $this->enviarPushGamificacion($gamificacion, [
            'title' => 'Logro desbloqueado: ' . $logro->titulo,
            'body' => $logro->descripcion,
        ]);
    }

    private function notificarHitoRacha(Gamificacion $gamificacion, int $hito): void
    {
        Notificacion::create([
            'usuario_id' => $gamificacion->usuario_id,
            'titulo' => 'Hito de racha alcanzado',
            'mensaje' => "¡Increíble! Has mantenido una racha de {$hito} días.",
            'tipo' => 'hito_racha',
            'url' => NotificacionService::urlFrontend('progress'),
        ]);

        $this->enviarPushGamificacion($gamificacion, [
            'title' => 'Hito de racha alcanzado',
            'body' => "¡Increíble! Has mantenido una racha de {$hito} días.",
        ]);
    }

    private function enviarPushGamificacion(Gamificacion $gamificacion, array $datos): void
    {
        try {
            $usuario = User::find($gamificacion->usuario_id);
            if ($usuario) {
                app(PushNotificationService::class)->sendToUser(
                    $usuario,
                    PushNotificationService::payload(
                        $datos['title'],
                        $datos['body'],
                        NotificacionService::urlFrontend('progress')
                    )
                );
            }
        } catch (\Throwable $e) {
            \Log::error('Error al enviar push de gamificación', ['exception' => $e]);
        }
    }

    private function tareaPerteneceAlUsuario(Tarea $tarea): bool
    {
        $usuarioId = Auth::id();

        if ($tarea->creador_id === $usuarioId || $tarea->responsable_id === $usuarioId) {
            return true;
        }

        if ($tarea->espacio_trabajo_id) {
            $espacio = EspacioTrabajo::with('equipo')->find($tarea->espacio_trabajo_id);
            if ($espacio && $espacio->equipo) {
                return MiembroEquipo::where('equipo_id', $espacio->equipo_id)
                    ->where('usuario_id', $usuarioId)
                    ->exists();
            }
        }

        return false;
    }
}
