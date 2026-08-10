<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Equipo;
use App\Models\EspacioTrabajo;
use App\Models\MiembroEquipo;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EspacioTrabajoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $espacios = EspacioTrabajo::with('equipo.miembros', 'tareas')
            ->whereHas('equipo.miembros', function ($q) {
                $q->where('usuario_id', Auth::id());
            })
            ->orWhereHas('equipo', function ($q) {
                $q->where('propietario_id', Auth::id());
            })
            ->orderBy('orden')
            ->get();
        return response()->json(['success' => true, 'data' => $espacios]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icono' => 'nullable|string|max:50',
            'orden' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $espacio = EspacioTrabajo::create($validated);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => EspacioTrabajo::class,
                'objeto_id' => $espacio->id,
                'descripcion' => "Creó el espacio de trabajo {$espacio->nombre}",
            ]);

            $miembrosEquipo = MiembroEquipo::where('equipo_id', $validated['equipo_id'])
                ->where('usuario_id', '!=', Auth::id())
                ->pluck('usuario_id');
            $urlEspacio = NotificacionService::urlFrontend("espacios/{$espacio->id}");
            foreach ($miembrosEquipo as $miembroId) {
                NotificacionService::notificar(
                    $miembroId,
                    'Nuevo espacio de trabajo',
                    "Se creó el espacio de trabajo {$espacio->nombre} en el equipo",
                    'sistema',
                    $urlEspacio
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $espacio, 'message' => 'Espacio de trabajo creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear espacio de trabajo'], 500);
        }
    }

    public function show($id)
    {
        $espacio = EspacioTrabajo::with(['equipo.miembros', 'estados', 'tablas', 'etiquetas'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $espacio]);
    }

    public function update(Request $request, $id)
    {
        $espacio = EspacioTrabajo::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icono' => 'nullable|string|max:50',
            'orden' => 'nullable|integer',
        ]);

        $espacio->update($validated);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'objeto_type' => EspacioTrabajo::class,
            'objeto_id' => $espacio->id,
            'descripcion' => "Editó el espacio de trabajo {$espacio->nombre}",
        ]);

        return response()->json(['success' => true, 'data' => $espacio, 'message' => 'Espacio de trabajo actualizado']);
    }

    public function destroy($id)
    {
        $espacio = EspacioTrabajo::findOrFail($id);
        DB::beginTransaction();
        try {
            $nombre = $espacio->nombre;
            $espacio->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => EspacioTrabajo::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó el espacio de trabajo {$nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Espacio de trabajo eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar espacio de trabajo'], 500);
        }
    }
}
