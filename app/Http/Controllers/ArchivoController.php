<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($tareaId)
    {
        $archivos = Archivo::where('tarea_id', $tareaId)->get();
        return response()->json(['success' => true, 'data' => $archivos]);
    }

    public function store(Request $request, $tareaId)
    {
        $validated = $request->validate([
            'archivo' => 'required|file|max:102400',
            'nombre' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('archivo');
            $path = $file->store('tareas/' . $tareaId, 'public');

            $archivo = Archivo::create([
                'tarea_id' => $tareaId,
                'nombre' => $validated['nombre'] ?? $file->getClientOriginalName(),
                'url' => Storage::url($path),
                'tipo' => $file->getMimeType(),
                'peso' => $file->getSize(),
                'subido_por' => Auth::id(),
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'subir_archivo',
                'objeto_type' => Archivo::class,
                'objeto_id' => $archivo->id,
                'descripcion' => "Subió archivo {$archivo->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $archivo, 'message' => 'Archivo subido'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al subir archivo'], 500);
        }
    }

    public function show($id)
    {
        $archivo = Archivo::findOrFail($id);
        return response()->json(['success' => true, 'data' => $archivo]);
    }

    public function destroy($id)
    {
        $archivo = Archivo::findOrFail($id);
        DB::beginTransaction();
        try {
            $path = str_replace('/storage/', '', $archivo->url);
            Storage::disk('public')->delete($path);
            $archivo->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Archivo::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó archivo {$archivo->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Archivo eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar archivo'], 500);
        }
    }

    public function descargar($id)
    {
        $archivo = Archivo::findOrFail($id);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'descargar',
            'objeto_type' => Archivo::class,
            'objeto_id' => $archivo->id,
            'descripcion' => "Descargó archivo {$archivo->nombre}",
        ]);

        $path = str_replace('/storage/', '', $archivo->url);
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
        }
        return Storage::disk('public')->download($path, $archivo->nombre);
    }
}
