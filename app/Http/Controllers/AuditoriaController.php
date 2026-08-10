<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $auditorias = Auditoria::with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        return response()->json(['success' => true, 'data' => $auditorias]);
    }

    public function show($id)
    {
        $auditoria = Auditoria::with('usuario')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $auditoria]);
    }
}
