<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $estados = Estado::with('workspace')->orderBy('orden')->get();
        return response()->json(['success' => true, 'data' => $estados]);
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
            $estado = Estado::create($validated);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Estado::class,
                'objeto_id' => $estado->id,
                'descripcion' => "Creó el estado {$estado->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $estado, 'message' => 'Estado creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear estado'], 500);
        }
    }

    public function show($id)
    {
        $estado = Estado::with(['workspace', 'tareas'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $estado]);
    }

    public function update(Request $request, $id)
    {
        $estado = Estado::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:20',
            'orden' => 'nullable|integer',
        ]);

        $estado->update($validated);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'objeto_type' => Estado::class,
            'objeto_id' => $estado->id,
            'descripcion' => "Editó el estado {$estado->nombre}",
        ]);

        return response()->json(['success' => true, 'data' => $estado, 'message' => 'Estado actualizado']);
    }

    public function destroy($id)
    {
        $estado = Estado::findOrFail($id);
        DB::beginTransaction();
        try {
            $nombre = $estado->nombre;
            $estado->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Estado::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó el estado {$nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Estado eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar estado'], 500);
        }
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*.id' => 'required|exists:estados,id',
            'orden.*.orden' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->orden as $item) {
                Estado::where('id', $item['id'])->update(['orden' => $item['orden']]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reordenar'], 500);
        }
    }
}
