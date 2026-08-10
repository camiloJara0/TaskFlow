<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Tabla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TablaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $tablas = Tabla::with('workspace')->orderBy('orden')->get();
        return response()->json(['success' => true, 'data' => $tablas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workspace_id' => 'required|exists:espacios_trabajo,id',
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'orden' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $tabla = Tabla::create($validated);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Tabla::class,
                'objeto_id' => $tabla->id,
                'descripcion' => "Creó la tabla {$tabla->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $tabla, 'message' => 'Tabla creada'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear tabla'], 500);
        }
    }

    public function show($id)
    {
        $tabla = Tabla::with(['workspace', 'tareas.estado', 'tareas.responsable'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $tabla]);
    }

    public function update(Request $request, $id)
    {
        $tabla = Tabla::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:20',
            'orden' => 'nullable|integer',
        ]);

        $tabla->update($validated);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'objeto_type' => Tabla::class,
            'objeto_id' => $tabla->id,
            'descripcion' => "Editó la tabla {$tabla->nombre}",
        ]);

        return response()->json(['success' => true, 'data' => $tabla, 'message' => 'Tabla actualizada']);
    }

    public function destroy($id)
    {
        $tabla = Tabla::findOrFail($id);
        DB::beginTransaction();
        try {
            $nombre = $tabla->nombre;
            $tabla->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Tabla::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó la tabla {$nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tabla eliminada']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar tabla'], 500);
        }
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*.id' => 'required|exists:tablas,id',
            'orden.*.orden' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->orden as $item) {
                Tabla::where('id', $item['id'])->update(['orden' => $item['orden']]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reordenar'], 500);
        }
    }
}
