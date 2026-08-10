<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Reaccione;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReaccionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comentario_id' => 'required|exists:comentarios,id',
            'tipo' => 'required',
        ]);

        $existe = Reaccione::where('usuario_id', Auth::id())
            ->where('comentario_id', $validated['comentario_id'])
            ->where('tipo', $validated['tipo'])
            ->exists();

        if ($existe) {
            return response()->json(['success' => false, 'message' => 'Ya reaccionaste así'], 409);
        }

        $reaccion = Reaccione::create([
            ...$validated,
            'usuario_id' => Auth::id(),
        ]);

        $comentario = Comentario::find($validated['comentario_id']);
        NotificacionService::notificar(
            $comentario->usuario_id,
            'Nueva reacción',
            (Auth::user()->nombre ?? 'Alguien') . " reaccionó con {$validated['tipo']} a tu comentario",
            'comentario',
            NotificacionService::urlFrontend("comentarios/{$comentario->id}")
        );
        if ($comentario) {
        }

        return response()->json(['success' => true, 'data' => $reaccion->load('usuario'), 'message' => 'Reacción agregada'], 201);
    }

    public function destroy($id)
    {
        $reaccion = Reaccione::findOrFail($id);
        if ($reaccion->usuario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        $reaccion->delete();
        return response()->json(['success' => true, 'message' => 'Reacción eliminada']);
    }
}
