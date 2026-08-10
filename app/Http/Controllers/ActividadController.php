<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActividadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId = null)
    {
        $query = Actividad::with('usuario')->orderBy('created_at', 'desc')->limit(100);
        if ($tareaId) {
            $query->where('objeto_type', 'App\Models\Tarea')->where('objeto_id', $tareaId);
        }
        $actividades = $query->get();
        return response()->json(['success' => true, 'data' => $actividades]);
    }

    public function show($id)
    {
        $actividad = Actividad::with('usuario')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $actividad]);
    }
}
