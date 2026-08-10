<?php

namespace App\Http\Controllers;

use App\Models\Subtarea;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubtareaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId)
    {
        $subtareas = Subtarea::where('tarea_id', $tareaId)->orderBy('orden')->get();
        return response()->json(['success' => true, 'data' => $subtareas]);
    }

    public function store(Request $request, $tareaId)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:500',
            'orden' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $subtarea = Subtarea::create([
                ...$validated,
                'tarea_id' => $tareaId,
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Subtarea::class,
                'objeto_id' => $subtarea->id,
                'descripcion' => "Creó subtarea {$subtarea->titulo}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $subtarea, 'message' => 'Subtarea creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear subtarea'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $subtarea = Subtarea::findOrFail($id);
        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:500',
            'orden' => 'nullable|integer',
        ]);

        $subtarea->update($validated);
        return response()->json(['success' => true, 'data' => $subtarea, 'message' => 'Subtarea actualizada']);
    }

    public function destroy($id)
    {
        $subtarea = Subtarea::findOrFail($id);
        $subtarea->delete();
        return response()->json(['success' => true, 'message' => 'Subtarea eliminada']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate(['estado' => 'required|boolean']);
        $subtarea = Subtarea::findOrFail($id);
        $subtarea->estado = $validated['estado'];
        $subtarea->save();
        return response()->json(['success' => true, 'data' => $subtarea, 'message' => $validated['estado'] ? 'Subtarea completada' : 'Subtarea pendiente']);
    }
}
