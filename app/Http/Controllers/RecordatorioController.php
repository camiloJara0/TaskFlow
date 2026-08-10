<?php

namespace App\Http\Controllers;

use App\Models\Recordatorio;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordatorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $recordatorios = Recordatorio::with('tarea')
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha')
            ->get();
        return response()->json(['success' => true, 'data' => $recordatorios]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => 'required|exists:tareas,id',
            'fecha' => 'required|date',
            'tipo' => 'sometimes|in:5_minutos,30_minutos,1_hora,1_dia,1_semana',
        ]);

        DB::beginTransaction();
        try {
            $recordatorio = Recordatorio::create([
                ...$validated,
                'usuario_id' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $recordatorio, 'message' => 'Recordatorio creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear recordatorio'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $recordatorio = Recordatorio::where('id', $id)->where('usuario_id', Auth::id())->firstOrFail();
        $validated = $request->validate([
            'fecha' => 'sometimes|date',
            'tipo' => 'sometimes|in:5_minutos,30_minutos,1_hora,1_dia,1_semana',
        ]);
        $recordatorio->update($validated);
        return response()->json(['success' => true, 'data' => $recordatorio, 'message' => 'Recordatorio actualizado']);
    }

    public function destroy($id)
    {
        $recordatorio = Recordatorio::where('id', $id)->where('usuario_id', Auth::id())->firstOrFail();
        $recordatorio->delete();
        return response()->json(['success' => true, 'message' => 'Recordatorio eliminado']);
    }
}
