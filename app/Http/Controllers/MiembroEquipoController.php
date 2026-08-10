<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionSistemaMail;
use App\Models\MiembroEquipo;
use App\Models\Auditoria;
use App\Models\Equipo;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiembroEquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($equipoId)
    {
        $miembros = MiembroEquipo::with('usuario')
            ->where('equipo_id', $equipoId)
            ->get();
        return response()->json(['success' => true, 'data' => $miembros]);
    }

    public function update(Request $request, $id)
    {
        $miembro = MiembroEquipo::findOrFail($id);
        $validated = $request->validate([
            'rol' => 'required|in:Owner,Administrador,Editor,Miembro,Invitado',
            'permisos' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $miembro->update($validated);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'editar',
                'objeto_type' => MiembroEquipo::class,
                'objeto_id' => $miembro->id,
                'descripcion' => "Actualizó rol de miembro del equipo",
            ]);

            $equipo = $miembro->equipo ?? Equipo::find($miembro->equipo_id);
            $nombreEquipo = $equipo->nombre ?? '';
            $destinatario = $miembro->usuario;
            NotificacionService::notificar(
                $miembro->usuario_id,
                'Cambio de rol',
                "Tu rol en el equipo {$nombreEquipo} ahora es {$miembro->rol}",
                'sistema',
                $equipo ? NotificacionService::urlFrontend("equipos/{$equipo->id}") : null,
                $destinatario ? new NotificacionSistemaMail($destinatario, [
                    'asunto' => "Tu rol cambió en {$nombreEquipo}",
                    'mensaje' => "Tu rol en el equipo {$nombreEquipo} ahora es {$miembro->rol}.",
                    'detalle_titulo' => $nombreEquipo,
                    'detalle_sub' => 'Rol actualizado por el administrador del equipo.',
                    'url' => $equipo ? NotificacionService::urlFrontend("equipos/{$equipo->id}") : null,
                    'boton_texto' => 'Ver equipo',
                ]) : null
            );

            DB::commit();
            return response()->json(['success' => true, 'data' => $miembro->load('usuario'), 'message' => 'Rol actualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar miembro'], 500);
        }
    }

    public function destroy($id)
    {
        $miembro = MiembroEquipo::findOrFail($id);
        $miembro->delete();
        return response()->json(['success' => true, 'message' => 'Miembro eliminado del equipo']);
    }
}
