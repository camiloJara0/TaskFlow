<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionReunionMail;
use App\Models\Actividad;
use App\Models\Auditoria;
use App\Models\Reunion;
use App\Models\ReunionIntegrante;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReunionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $reuniones = Reunion::with(['espacioTrabajo', 'creador', 'integrantes.usuario'])
            ->where(function ($q) {
                $q->where('creador_id', Auth::id())
                    ->orWhereHas('integrantes', function ($q2) {
                        $q2->where('id_usuario', Auth::id());
                    });
            })
            ->when($request->espacio_trabajo_id, function ($q, $espacioId) {
                $q->where('espacio_trabajo_id', $espacioId);
            })
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return response()->json(['success' => true, 'data' => $reuniones]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'espacio_trabajo_id' => 'nullable|exists:espacios_trabajo,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,confirmada,en_curso,finalizada,cancelada',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'url' => 'nullable|string|max:500',
            'archivada' => 'sometimes|boolean',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $reunion = Reunion::create([
                ...$validated,
                'creador_id' => Auth::id(),
                'estado' => $validated['estado'] ?? 'pendiente',
            ]);

            $this->sincronizarIntegrantes($reunion, $request->input('integrantes', []));
            $this->notificarInvitados($reunion);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Reunion::class,
                'objeto_id' => $reunion->id,
                'descripcion' => "Creó la reunión {$reunion->titulo}",
            ]);

            Actividad::create([
                'usuario_id' => Auth::id(),
                'tipo' => 'crear',
                'objeto_type' => Reunion::class,
                'objeto_id' => $reunion->id,
                'descripcion' => "Creó la reunión '{$reunion->titulo}'",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $reunion->load(['espacioTrabajo', 'creador', 'integrantes.usuario']),
                'message' => 'Reunión creada',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear la reunión', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Reunion $reunion)
    {
        $this->autorizar($reunion);

        return response()->json([
            'success' => true,
            'data' => $reunion->load(['espacioTrabajo', 'creador', 'integrantes.usuario']),
        ]);
    }

    public function update(Request $request, Reunion $reunion)
    {
        $this->autorizar($reunion);

        $validated = $request->validate([
            'espacio_trabajo_id' => 'nullable|exists:espacios_trabajo,id',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,confirmada,en_curso,finalizada,cancelada',
            'fecha' => 'sometimes|date',
            'hora' => 'sometimes|date_format:H:i',
            'url' => 'nullable|string|max:500',
            'archivada' => 'sometimes|boolean',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $reprogramada = false;
            if ($request->has('fecha') || $request->has('hora')) {
                $nuevaFecha = $request->input('fecha', $reunion->fecha->format('Y-m-d'));
                $nuevaHora = $request->input('hora', $reunion->hora);
                if ($nuevaFecha !== $reunion->fecha->format('Y-m-d') || $nuevaHora !== $reunion->hora) {
                    $reprogramada = true;
                    $validated['recordatorio_enviado'] = false;
                }
            }

            $reunion->update($validated);

            if ($request->has('integrantes')) {
                $anteriores = $reunion->integrantes()->pluck('id_usuario')->all();
                $nuevos = $this->sincronizarIntegrantes($reunion, $request->input('integrantes', []));
                $agregados = array_values(array_diff($nuevos, $anteriores));
                $this->notificarInvitados($reunion, $agregados);
            }

            if ($reprogramada) {
                $this->notificarReprogramacion($reunion);
            }

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'actualizar',
                'objeto_type' => Reunion::class,
                'objeto_id' => $reunion->id,
                'descripcion' => "Actualizó la reunión {$reunion->titulo}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $reunion->load(['espacioTrabajo', 'creador', 'integrantes.usuario']),
                'message' => 'Reunión actualizada',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar la reunión', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Reunion $reunion)
    {
        $this->autorizar($reunion);

        $titulo = $reunion->titulo;

        DB::beginTransaction();
        try {
            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Reunion::class,
                'objeto_id' => $reunion->id,
                'descripcion' => "Eliminó la reunión {$titulo}",
            ]);

            $reunion->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Reunión eliminada']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar la reunión', 'error' => $e->getMessage()], 500);
        }
    }

    public function cambiarEstado(Request $request, Reunion $reunion)
    {
        $this->autorizar($reunion);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmada,en_curso,finalizada,cancelada',
        ]);

        $reunion->update(['estado' => $validated['estado']]);

        $this->notificarCambioEstado($reunion, $validated['estado']);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'cambiar estado',
            'objeto_type' => Reunion::class,
            'objeto_id' => $reunion->id,
            'descripcion' => "Cambió el estado de la reunión {$reunion->titulo} a {$validated['estado']}",
        ]);

        return response()->json([
            'success' => true,
            'data' => $reunion->load(['espacioTrabajo', 'creador', 'integrantes.usuario']),
            'message' => 'Estado actualizado',
        ]);
    }

    public function toggleArchivar(Reunion $reunion)
    {
        $this->autorizar($reunion);

        $reunion->update(['archivada' => !$reunion->archivada]);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'archivar',
            'objeto_type' => Reunion::class,
            'objeto_id' => $reunion->id,
            'descripcion' => $reunion->archivada ? "Archivó la reunión {$reunion->titulo}" : "Desarchivó la reunión {$reunion->titulo}",
        ]);

        return response()->json([
            'success' => true,
            'data' => $reunion,
            'message' => $reunion->archivada ? 'Reunión archivada' : 'Reunión desarchivada',
        ]);
    }

    private function autorizar(Reunion $reunion): void
    {
        $esIntegrante = $reunion->integrantes()->where('id_usuario', Auth::id())->exists();

        if ($reunion->creador_id !== Auth::id() && !$esIntegrante) {
            abort(403, 'No tienes acceso a esta reunión');
        }
    }

    private function sincronizarIntegrantes(Reunion $reunion, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (!in_array($reunion->creador_id, $ids, true)) {
            $ids[] = $reunion->creador_id;
        }

        $reunion->integrantes()->delete();

        foreach ($ids as $usuarioId) {
            ReunionIntegrante::create([
                'id_reunion' => $reunion->id,
                'id_usuario' => $usuarioId,
            ]);
        }

        return $ids;
    }

    private function notificarInvitados(Reunion $reunion, array $usuariosIds = []): void
    {
        $ids = $usuariosIds ?: $reunion->integrantes()->pluck('id_usuario')->all();
        $url = NotificacionService::urlFrontend("reuniones/{$reunion->id}");

        foreach ($ids as $usuarioId) {
            if ($usuarioId === $reunion->creador_id) {
                continue;
            }

            $usuario = User::find($usuarioId);

            NotificacionService::notificar(
                $usuarioId,
                'Nueva reunión',
                "Te invitaron a la reunión {$reunion->titulo} el {$reunion->fecha->format('d/m/Y')} a las {$reunion->hora}",
                'reunion',
                $url,
                $usuario ? new NotificacionReunionMail($usuario, [
                    'asunto' => "Invitación a reunión: {$reunion->titulo}",
                    'organizador' => $reunion->creador->nombre ?? 'Alguien',
                    'reunion' => [
                        'titulo' => $reunion->titulo,
                        'descripcion' => $reunion->descripcion,
                        'fecha' => $reunion->fecha->format('d/m/Y'),
                        'hora' => $reunion->hora,
                        'url' => $reunion->url,
                    ],
                    'url' => $url,
                ]) : null
            );
        }
    }

    private function notificarReprogramacion(Reunion $reunion): void
    {
        $url = NotificacionService::urlFrontend("reuniones/{$reunion->id}");

        foreach ($reunion->integrantes as $integrante) {
            if ($integrante->id_usuario === $reunion->creador_id) {
                continue;
            }

            NotificacionService::notificar(
                $integrante->id_usuario,
                'Reunión reprogramada',
                "La reunión {$reunion->titulo} cambió al {$reunion->fecha->format('d/m/Y')} a las {$reunion->hora}",
                'cambio_estado',
                $url
            );
        }
    }

    private function notificarCambioEstado(Reunion $reunion, string $estado): void
    {
        $url = NotificacionService::urlFrontend("reuniones/{$reunion->id}");
        $mensajes = [
            'confirmada' => "La reunión {$reunion->titulo} fue confirmada",
            'en_curso' => "La reunión {$reunion->titulo} comenzó",
            'finalizada' => "La reunión {$reunion->titulo} finalizó",
            'cancelada' => "La reunión {$reunion->titulo} fue cancelada",
        ];

        if (!isset($mensajes[$estado])) {
            return;
        }

        foreach ($reunion->integrantes as $integrante) {
            if ($integrante->id_usuario === $reunion->creador_id) {
                continue;
            }

            NotificacionService::notificar(
                $integrante->id_usuario,
                'Estado de reunión',
                $mensajes[$estado],
                'cambio_estado',
                $url
            );
        }
    }
}
