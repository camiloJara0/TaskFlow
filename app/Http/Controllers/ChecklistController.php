<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChecklistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId)
    {
        $checklists = Checklist::where('tarea_id', $tareaId)->orderBy('orden')->get();
        return response()->json(['success' => true, 'data' => $checklists]);
    }

    public function store(Request $request, $tareaId)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:500',
            'orden' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $checklist = Checklist::create([
                ...$validated,
                'tarea_id' => $tareaId,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $checklist, 'message' => 'Item creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear item'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $checklist = Checklist::findOrFail($id);
        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:500',
            'orden' => 'nullable|integer',
        ]);
        $checklist->update($validated);
        return response()->json(['success' => true, 'data' => $checklist, 'message' => 'Item actualizado']);
    }

    public function destroy($id)
    {
        Checklist::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Item eliminado']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate(['estado' => 'required|boolean']);
        $checklist = Checklist::findOrFail($id);
        $checklist->estado = $validated['estado'];
        $checklist->save();
        return response()->json(['success' => true, 'data' => $checklist, 'message' => $validated['estado'] ? 'Item completado' : 'Item pendiente']);
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*.id' => 'required|exists:checklists,id',
            'orden.*.orden' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->orden as $item) {
                Checklist::where('id', $item['id'])->update(['orden' => $item['orden']]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reordenar'], 500);
        }
    }
}
