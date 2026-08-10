<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionComentarioMail;
use App\Models\Comentario;
use App\Models\Auditoria;
use App\Models\Actividad;
use App\Models\Tarea;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComentarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId)
    {
        $comentarios = Comentario::with(['usuario', 'reacciones.usuario'])
            ->where('tarea_id', $tareaId)
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json(['success' => true, 'data' => $comentarios]);
    }

    public function store(Request $request, $tareaId)
    {
        $validated = $request->validate([
            'comentario' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $comentario = Comentario::create([
                'tarea_id' => $tareaId,
                'usuario_id' => Auth::id(),
                'comentario' => $validated['comentario'],
            ]);

            Actividad::create([
                'usuario_id' => Auth::id(),
                'tipo' => 'comentario',
                'objeto_type' => Comentario::class,
                'objeto_id' => $comentario->id,
                'descripcion' => 'Agregó un comentario',
            ]);

            $tarea = Tarea::with('creador')->find($tareaId);
            if ($tarea) {
                $nombreAutor = $comentario->usuario->nombre ?? 'Alguien';
                $destinatarios = array_unique(array_filter([
                    $tarea->responsable_id,
                    $tarea->creador_id,
                ]));
                $urlTarea = NotificacionService::urlFrontend("tareas/{$tarea->id}");
                foreach ($destinatarios as $destinatarioId) {
                    $destinatario = User::find($destinatarioId);
                    NotificacionService::notificar(
                        $destinatarioId,
                        'Nuevo comentario',
                        "{$nombreAutor} comentó en la tarea {$tarea->titulo}",
                        'comentario',
                        $urlTarea,
                        $destinatario ? new NotificacionComentarioMail($destinatario, [
                            'asunto' => "Nuevo comentario en '{$tarea->titulo}'",
                            'autor' => $nombreAutor,
                            'comentario' => $comentario->comentario,
                            'tarea' => ['titulo' => $tarea->titulo],
                            'url' => $urlTarea,
                        ]) : null
                    );
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $comentario->load('usuario'), 'message' => 'Comentario creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear comentario'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $comentario = Comentario::findOrFail($id);
        if ($comentario->usuario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $validated = $request->validate(['comentario' => 'required|string']);
        $comentario->update($validated);
        return response()->json(['success' => true, 'data' => $comentario, 'message' => 'Comentario actualizado']);
    }

    public function destroy($id)
    {
        $comentario = Comentario::findOrFail($id);
        if ($comentario->usuario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        $comentario->delete();
        return response()->json(['success' => true, 'message' => 'Comentario eliminado']);
    }
}
