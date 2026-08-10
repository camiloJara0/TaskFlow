<?php

namespace App\Http\Controllers;

use App\Models\Automatizacion;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AutomatizacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($workspaceId)
    {
        $automatizaciones = Automatizacion::where('workspace_id', $workspaceId)->get();
        return response()->json(['success' => true, 'data' => $automatizaciones]);
    }

    public function store(Request $request, $workspaceId)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'evento' => 'required|string|max:100',
            'condicion' => 'nullable|array',
            'accion' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $automatizacion = Automatizacion::create([
                ...$validated,
                'workspace_id' => $workspaceId,
                'condicion' => $validated['condicion'] ?? null,
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Automatizacion::class,
                'objeto_id' => $automatizacion->id,
                'descripcion' => "Creó automatización {$automatizacion->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $automatizacion, 'message' => 'Automatización creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear automatización'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $automatizacion = Automatizacion::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'evento' => 'sometimes|string|max:100',
            'condicion' => 'nullable|array',
            'accion' => 'sometimes|array',
        ]);
        $automatizacion->update($validated);
        return response()->json(['success' => true, 'data' => $automatizacion, 'message' => 'Automatización actualizada']);
    }

    public function destroy($id)
    {
        $automatizacion = Automatizacion::findOrFail($id);
        $automatizacion->delete();
        return response()->json(['success' => true, 'message' => 'Automatización eliminada']);
    }

    public function toggleActivo($id)
    {
        $automatizacion = Automatizacion::findOrFail($id);
        $automatizacion->activo = !$automatizacion->activo;
        $automatizacion->save();
        return response()->json(['success' => true, 'data' => $automatizacion, 'message' => $automatizacion->activo ? 'Automatización activada' : 'Automatización desactivada']);
    }
}
