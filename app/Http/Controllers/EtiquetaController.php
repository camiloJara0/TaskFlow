<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Etiqueta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EtiquetaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $etiquetas = Etiqueta::with('workspace')->get();
        return response()->json(['success' => true, 'data' => $etiquetas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workspace_id' => 'required|exists:espacios_trabajo,id',
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'icono' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $etiqueta = Etiqueta::create($validated);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Etiqueta::class,
                'objeto_id' => $etiqueta->id,
                'descripcion' => "Creó la etiqueta {$etiqueta->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $etiqueta, 'message' => 'Etiqueta creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear etiqueta'], 500);
        }
    }

    public function show($id)
    {
        $etiqueta = Etiqueta::with('workspace')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $etiqueta]);
    }

    public function update(Request $request, $id)
    {
        $etiqueta = Etiqueta::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:20',
            'icono' => 'nullable|string|max:50',
        ]);

        $etiqueta->update($validated);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'objeto_type' => Etiqueta::class,
            'objeto_id' => $etiqueta->id,
            'descripcion' => "Editó la etiqueta {$etiqueta->nombre}",
        ]);

        return response()->json(['success' => true, 'data' => $etiqueta, 'message' => 'Etiqueta actualizada']);
    }

    public function destroy($id)
    {
        $etiqueta = Etiqueta::findOrFail($id);
        DB::beginTransaction();
        try {
            $nombre = $etiqueta->nombre;
            $etiqueta->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Etiqueta::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó la etiqueta {$nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Etiqueta eliminada']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar etiqueta'], 500);
        }
    }
}
