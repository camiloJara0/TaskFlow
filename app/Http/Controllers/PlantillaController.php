<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\Tarea;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantillaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($workspaceId)
    {
        $plantillas = Plantilla::with('creador')
            ->where('workspace_id', $workspaceId)
            ->get();
        return response()->json(['success' => true, 'data' => $plantillas]);
    }

    public function store(Request $request, $workspaceId)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'datos' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $plantilla = Plantilla::create([
                ...$validated,
                'workspace_id' => $workspaceId,
                'creado_por' => Auth::id(),
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Plantilla::class,
                'objeto_id' => $plantilla->id,
                'descripcion' => "Creó plantilla {$plantilla->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $plantilla, 'message' => 'Plantilla creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear plantilla'], 500);
        }
    }

    public function show($id)
    {
        $plantilla = Plantilla::with('creador')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $plantilla]);
    }

    public function destroy($id)
    {
        $plantilla = Plantilla::findOrFail($id);
        $plantilla->delete();
        return response()->json(['success' => true, 'message' => 'Plantilla eliminada']);
    }

    public function aplicar($id, Request $request)
    {
        $plantilla = Plantilla::findOrFail($id);
        $validated = $request->validate([
            'workspace_id' => 'required|exists:espacios_trabajo,id',
            'proyecto_nombre' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // $proyecto = Proyecto::create([
            //     'workspace_id' => $validated['workspace_id'],
            //     'nombre' => $validated['proyecto_nombre'],
            //     'descripcion' => 'Creado desde plantilla: ' . $plantilla->nombre,
            // ]);

            $datos = $plantilla->datos;
            if (isset($datos['tareas'])) {
                foreach ($datos['tareas'] as $tareaData) {
                    Tarea::create([
                        'espacio_trabajo_id' => $request->workspace_id,
                        'lista_id' => $tareaData['lista_id'] ?? null,
                        'creador_id' => Auth::id(),
                        'titulo' => $tareaData['titulo'],
                        'descripcion' => $tareaData['descripcion'] ?? null,
                        'estado_id' => $tareaData['estado_id'] ?? null,
                        'prioridad' => $tareaData['prioridad'] ?? 'Media',
                        'orden' => $tareaData['orden'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $workspaceId, 'message' => 'Plantilla aplicada correctamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al aplicar plantilla'], 500);
        }
    }
}
