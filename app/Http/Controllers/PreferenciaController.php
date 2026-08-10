<?php

namespace App\Http\Controllers;

use App\Models\Preferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configuracion = Preferencia::where('usuario_id', Auth::id())->first();

        return response()->json([
            'success' => true,
            'data' => $configuracion->apariencia
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Preferencia  $preferencia
     * @return \Illuminate\Http\Response
     */
    public function show(Preferencia $preferencia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Preferencia  $preferencia
     * @return \Illuminate\Http\Response
     */
    public function edit(Preferencia $preferencia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Preferencia  $preferencia
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Preferencia $preferencia)
    {
        $validated = $request->validate([
            'font_size'=> 'nullable|in:small,medium,large',
            'animaciones_reducidas'=> 'nullable|boolean',
            'imagen_fondo'=> 'nullable|string|max:1000',
            'tema_solido'=> 'nullable|boolean'
        ]);

        $user = Auth::id();

        $preferencia = Preferencia::where('usuario_id', $user)->first();

        if($preferencia) {
            Preferencia::update([
                'usuario_id' => $user,
                'preferencia' => $validated
            ]);
        } else {
            Preferencia::create([
                'usuario_id' => $user,
                'preferencia' => $validated
            ]);
        };

        return response()->json([
            'success' => true,
            'message' => 'Preferencia asiganda'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Preferencia  $preferencia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Preferencia $preferencia)
    {
        //
    }
}
