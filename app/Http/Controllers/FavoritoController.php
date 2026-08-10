<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $favoritos = Favorito::with('favoritable')
            ->where('usuario_id', Auth::id())
            ->get();
        return response()->json(['success' => true, 'data' => $favoritos]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'favoritable_type' => 'required|string',
            'favoritable_id' => 'required|integer',
        ]);

        $existe = Favorito::where('usuario_id', Auth::id())
            ->where('favoritable_type', $validated['favoritable_type'])
            ->where('favoritable_id', $validated['favoritable_id'])
            ->exists();

        if ($existe) {
            return response()->json(['success' => false, 'message' => 'Ya está en favoritos'], 409);
        }

        $favorito = Favorito::create([
            ...$validated,
            'usuario_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'data' => $favorito->load('favoritable'), 'message' => 'Agregado a favoritos'], 201);
    }

    public function destroy($id)
    {
        $favorito = Favorito::where('id', $id)->where('usuario_id', Auth::id())->firstOrFail();
        $favorito->delete();
        return response()->json(['success' => true, 'message' => 'Eliminado de favoritos']);
    }
}
