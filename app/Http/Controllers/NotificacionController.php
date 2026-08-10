<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $notificaciones = Notificacion::where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $notificaciones]);
    }

    public function marcarLeida($id)
    {
        $notificacion = Notificacion::where('id', $id)->where('usuario_id', Auth::id())->firstOrFail();
        $notificacion->update(['leida' => true]);
        return response()->json(['success' => true, 'data' => $notificacion, 'message' => 'Notificación marcada como leída']);
    }

    public function marcarTodasLeidas()
    {
        Notificacion::where('usuario_id', Auth::id())->where('leida', false)
            ->update(['leida' => true]);
        return response()->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    }

    public function destroy($id)
    {
        $notificacion = Notificacion::where('id', $id)->where('usuario_id', Auth::id())->firstOrFail();
        $notificacion->delete();
        return response()->json(['success' => true, 'message' => 'Notificación eliminada']);
    }
}
