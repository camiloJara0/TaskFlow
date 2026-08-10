<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DependenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId)
    {
        $dependencias = Dependencia::with(['tareaPadre', 'tareaHija'])
            ->where('tarea_padre_id', $tareaId)
            ->orWhere('tarea_hija_id', $tareaId)
            ->get();
        return response()->json(['success' => true, 'data' => $dependencias]);
    }

    public function store(Request $request, $tareaId)
    {
        $validated = $request->validate([
            'tarea_hija_id' => 'required|exists:tareas,id|different:tarea_padre_id',
            'tipo' => 'sometimes|in:fin_a_inicio,inicio_a_inicio,fin_a_fin',
        ]);

        $existe = Dependencia::where('tarea_padre_id', $tareaId)
            ->where('tarea_hija_id', $validated['tarea_hija_id'])
            ->exists();

        if ($existe) {
            return response()->json(['success' => false, 'message' => 'La dependencia ya existe'], 409);
        }

        DB::beginTransaction();
        try {
            $dependencia = Dependencia::create([
                'tarea_padre_id' => $tareaId,
                'tarea_hija_id' => $validated['tarea_hija_id'],
                'tipo' => $validated['tipo'] ?? 'fin_a_inicio',
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Dependencia::class,
                'objeto_id' => $dependencia->id,
                'descripcion' => 'Creó dependencia entre tareas',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $dependencia, 'message' => 'Dependencia creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear dependencia'], 500);
        }
    }

    public function destroy($id)
    {
        $dependencia = Dependencia::findOrFail($id);
        $dependencia->delete();
        return response()->json(['success' => true, 'message' => 'Dependencia eliminada']);
    }
}
