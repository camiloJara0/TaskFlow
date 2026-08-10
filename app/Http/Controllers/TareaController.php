<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionAsignacionMail;
use App\Mail\NotificacionCambioEstadoMail;
use App\Models\Tarea;
use App\Models\Auditoria;
use App\Models\Actividad;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TareaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $tareas = Tarea::with(['proyecto', 'lista', 'creador', 'responsable', 'etiquetas', 'subtareas'])
            ->where('archivada', false)
            ->where('creador_id', Auth::id())
            // ->where(function ($query) {
            //     $query->where('creador_id', Auth::id())
            //         ->orWhere('responsable_id', Auth::id());
            // })
            ->orderBy('orden')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $tareas]);
    }

    public function porWorkspace($id)
    {
        $tareas = Tarea::with(['proyecto', 'lista', 'creador', 'responsable', 'etiquetas', 'subtareas', 'comentarios', 'archivos'])
            ->where('archivada', false)
            ->where('espacio_trabajo_id', $id)
            ->orderBy('orden')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $tareas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'espacio_trabajo_id' => 'nullable|exists:espacios_trabajo,id',
            'responsable_id' => 'nullable|exists:users,id',
            'titulo' => 'required|string|max:500',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|string',
            'prioridad' => 'sometimes|in:Alta,Media,Baja,Urgente',
            'fecha_inicio' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'estimacion_horas' => 'nullable|numeric|min:0',
            'orden' => 'nullable|integer',
            'es_recurrente' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {

            $tarea = Tarea::create([
                ...$validated,
                'creador_id' => Auth::id(),
            ]);

            if ($request->has('etiquetas')) {
                $tarea->etiquetas()->sync($request->etiquetas);
            }

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Tarea::class,
                'objeto_id' => $tarea->id,
                'descripcion' => "Creó la tarea {$tarea->titulo}",
            ]);

            Actividad::create([
                'usuario_id' => Auth::id(),
                'tipo' => 'crear',
                'objeto_type' => Tarea::class,
                'objeto_id' => $tarea->id,
                'descripcion' => "Creó la tarea '{$tarea->titulo}'",
            ]);

            if (!empty($tarea->responsable_id)) {
                $responsable = User::find($tarea->responsable_id);
                $urlTarea = NotificacionService::urlFrontend("tareas/{$tarea->id}");
                NotificacionService::notificar(
                    $tarea->responsable_id,
                    'Tarea asignada',
                    "Se te asignó la tarea {$tarea->titulo}",
                    'asignacion',
                    $urlTarea,
                    $responsable ? new NotificacionAsignacionMail($responsable, [
                        'asunto' => "Nueva tarea asignada: {$tarea->titulo}",
                        'asignador' => Auth::user()->nombre ?? 'Alguien',
                        'tarea' => [
                            'titulo' => $tarea->titulo,
                            'descripcion' => $tarea->descripcion,
                            'prioridad' => $tarea->prioridad,
                            'fecha_vencimiento' => $tarea->fecha_vencimiento ? $tarea->fecha_vencimiento->format('d/m/Y H:i') : null,
                        ],
                        'url' => $urlTarea,
                    ]) : null
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $tarea->load(['estado', 'etiquetas', 'responsable']), 'message' => 'Tarea creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear tarea: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $tarea = Tarea::with(['proyecto', 'lista', 'creador', 'responsable', 'estado', 'etiquetas', 'subtareas', 'comentarios.usuario', 'comentarios.reacciones', 'archivos', 'checklists', 'dependenciasPadre.tareaPadre', 'dependenciasHija.tareaHija', 'recordatorios'])
            ->findOrFail($id);
        return response()->json(['success' => true, 'data' => $tarea]);
    }

    public function update(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);
        $validated = $request->validate([
            'espacio_trabajo_id' => 'nullable|exists:espacios_trabajo,id',
            'lista_id' => 'sometimes|exists:tablas,id',
            'responsable_id' => 'nullable|exists:users,id',
            'titulo' => 'sometimes|string|max:500',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|string',
            'prioridad' => 'sometimes|in:Alta,Media,Baja,Urgente',
            'fecha_inicio' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'estimacion_horas' => 'nullable|numeric|min:0',
            'horas_invertidas' => 'nullable|numeric|min:0',
            'porcentaje' => 'nullable|integer|min:0|max:100',
            'orden' => 'nullable|integer',
            'es_recurrente' => 'sometimes|boolean',
            'archivada' => 'sometimes|boolean',
            'etiquetas' => 'nullable|array',
            'etiquetas.*' => 'exists:etiquetas,id',
        ]);

        DB::beginTransaction();
        try {
            $responsableAnterior = $tarea->responsable_id;
            $cambios = [];
            foreach ($validated as $key => $value) {
                if ($key !== 'etiquetas' && $tarea->$key !== $value) {
                    $old = $tarea->$key;
                    $tarea->$key = $value;
                    if (in_array($key, ['prioridad', 'estado_id', 'responsable_id'])) {
                        $labels = ['prioridad' => 'prioridad', 'estado_id' => 'estado', 'responsable_id' => 'responsable'];
                        $label = isset($labels[$key]) ? $labels[$key] : $key;
                        $cambios[] = 'Cambió ' . $label;
                    }
                }
            }
            $tarea->save();

            if ($request->has('etiquetas')) {
                $tarea->etiquetas()->sync($request->etiquetas);
            }

            if (!empty($tarea->responsable_id) && $tarea->responsable_id !== $responsableAnterior) {
                $responsable = User::find($tarea->responsable_id);
                $urlTarea = NotificacionService::urlFrontend("tareas/{$tarea->id}");
                NotificacionService::notificar(
                    $tarea->responsable_id,
                    'Tarea asignada',
                    "Se te asignó la tarea {$tarea->titulo}",
                    'asignacion',
                    $urlTarea,
                    $responsable ? new NotificacionAsignacionMail($responsable, [
                        'asunto' => "Nueva tarea asignada: {$tarea->titulo}",
                        'asignador' => Auth::user()->nombre ?? 'Alguien',
                        'tarea' => [
                            'titulo' => $tarea->titulo,
                            'descripcion' => $tarea->descripcion,
                            'prioridad' => $tarea->prioridad,
                            'fecha_vencimiento' => $tarea->fecha_vencimiento ? $tarea->fecha_vencimiento->format('d/m/Y H:i') : null,
                        ],
                        'url' => $urlTarea,
                    ]) : null
                );
            }

            if (!empty($cambios)) {
                Actividad::create([
                    'usuario_id' => Auth::id(),
                    'tipo' => 'editar',
                    'objeto_type' => Tarea::class,
                    'objeto_id' => $tarea->id,
                    'descripcion' => implode(', ', $cambios),
                ]);
            }

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'editar',
                'objeto_type' => Tarea::class,
                'objeto_id' => $tarea->id,
                'descripcion' => "Editó la tarea {$tarea->titulo}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $tarea->load(['estado', 'etiquetas', 'responsable']), 'message' => 'Tarea actualizada']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar tarea'], 500);
        }
    }

    public function destroy($id)
    {
        $tarea = Tarea::findOrFail($id);
        DB::beginTransaction();
        try {
            $titulo = $tarea->titulo;
            $tarea->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Tarea::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó la tarea {$titulo}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tarea eliminada']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar tarea'], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate(['estado_id' => 'required|exists:estados,id']);
        $tarea = Tarea::findOrFail($id);
        $oldEstado = $tarea->estado->nombre ?? null;
        $tarea->estado_id = $validated['estado_id'];
        $tarea->save();
        $tarea->load('estado');

        Actividad::create([
            'usuario_id' => Auth::id(),
            'tipo' => 'cambio_estado',
            'objeto_type' => Tarea::class,
            'objeto_id' => $tarea->id,
            'descripcion' => "Cambió estado de '{$oldEstado}' a '{$tarea->estado->nombre}'",
        ]);

        $urlTarea = NotificacionService::urlFrontend("tareas/{$tarea->id}");
        $destinatarios = array_unique(array_filter([
            $tarea->responsable_id,
            $tarea->creador_id,
        ]));

        foreach ($destinatarios as $destinatarioId) {
            $destinatario = User::find($destinatarioId);
            NotificacionService::notificar(
                $destinatarioId,
                'Cambio de estado',
                "La tarea {$tarea->titulo} cambió a '{$tarea->estado->nombre}'",
                'cambio_estado',
                $urlTarea,
                $destinatario ? new NotificacionCambioEstadoMail($destinatario, [
                    'asunto' => "La tarea '{$tarea->titulo}' cambió de estado",
                    'tarea' => ['titulo' => $tarea->titulo],
                    'estado_anterior' => $oldEstado ?? 'Sin estado',
                    'estado_nuevo' => $tarea->estado->nombre ?? 'Sin estado',
                    'actor' => Auth::user()->nombre ?? 'Alguien',
                    'url' => $urlTarea,
                ]) : null
            );
        }

        return response()->json(['success' => true, 'data' => $tarea, 'message' => 'Estado actualizado']);
    }

    public function asignarResponsable(Request $request, $id)
    {
        $validated = $request->validate(['responsable_id' => 'nullable|exists:users,id']);
        $tarea = Tarea::findOrFail($id);
        $tarea->responsable_id = $validated['responsable_id'];
        $tarea->save();

        $nombre = $tarea->responsable->nombre ?? 'Sin asignar';
        Actividad::create([
            'usuario_id' => Auth::id(),
            'tipo' => 'asignacion',
            'objeto_type' => Tarea::class,
            'objeto_id' => $tarea->id,
            'descripcion' => "Asignó responsable: {$nombre}",
        ]);

        if (!empty($tarea->responsable_id)) {
            $responsable = $tarea->responsable;
            $urlTarea = NotificacionService::urlFrontend("tareas/{$tarea->id}");
            NotificacionService::notificar(
                $tarea->responsable_id,
                'Tarea asignada',
                "Se te asignó la tarea {$tarea->titulo}",
                'asignacion',
                $urlTarea,
                $responsable ? new NotificacionAsignacionMail($responsable, [
                    'asunto' => "Nueva tarea asignada: {$tarea->titulo}",
                    'asignador' => Auth::user()->nombre ?? 'Alguien',
                    'tarea' => [
                        'titulo' => $tarea->titulo,
                        'descripcion' => $tarea->descripcion,
                        'prioridad' => $tarea->prioridad,
                        'fecha_vencimiento' => $tarea->fecha_vencimiento ? $tarea->fecha_vencimiento->format('d/m/Y H:i') : null,
                    ],
                    'url' => $urlTarea,
                ]) : null
            );
        }

        return response()->json(['success' => true, 'data' => $tarea->load('responsable'), 'message' => 'Responsable asignado']);
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*.id' => 'required|exists:tareas,id',
            'orden.*.orden' => 'required|integer',
            'orden.*.lista_id' => 'sometimes|exists:tablas,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->orden as $item) {
                $update = ['orden' => $item['orden']];
                if (isset($item['lista_id'])) {
                    $update['lista_id'] = $item['lista_id'];
                }
                Tarea::where('id', $item['id'])->update($update);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reordenar'], 500);
        }
    }
}
