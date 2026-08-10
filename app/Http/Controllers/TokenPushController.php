<?php

namespace App\Http\Controllers;

use App\Models\TokenPush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenPushController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'p256dh' => 'nullable|string',
            'auth' => 'nullable|string',
            'navegador' => 'nullable|string|max:255',
        ]);

        TokenPush::updateOrCreate(
            ['usuario_id' => Auth::id(), 'token' => $validated['token']],
            [
                'navegador' => $validated['navegador'] ?? null,
                'p256dh' => $validated['p256dh'] ?? null,
                'auth' => $validated['auth'] ?? null,
                'activo' => true,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Token registrado']);
    }

    public function destroy($token)
    {
        $query = TokenPush::where('usuario_id', Auth::id());

        if (is_numeric($token)) {
            $query->where('id', $token);
        } else {
            $query->where('token', $token);
        }

        $registro = $query->first();
        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'Token no encontrado'], 404);
        }

        $registro->delete();
        return response()->json(['success' => true, 'message' => 'Token eliminado']);
    }
}
