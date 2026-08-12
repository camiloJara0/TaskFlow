<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionEquipoMail;
use App\Mail\NotificacionSistemaMail;
use App\Models\Auditoria;
use App\Models\Equipo;
use App\Models\MiembroEquipo;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $equipos = Equipo::with(['propietario', 'miembros'])
            ->where(function ($q) {
                $q->where('propietario_id', Auth::id())
                    ->orWhereHas('miembros', function ($q2) {
                        $q2->where('usuario_id', Auth::id());
                    });
            })
            ->get();
        return response()->json(['success' => true, 'data' => $equipos]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $equipo = Equipo::create([
                ...$validated,
                'propietario_id' => Auth::id(),
            ]);

            MiembroEquipo::create([
                'equipo_id' => $equipo->id,
                'usuario_id' => Auth::id(),
                'rol' => 'Owner',
                'fecha_ingreso' => now(),
            ]);

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'crear',
                'objeto_type' => Equipo::class,
                'objeto_id' => $equipo->id,
                'descripcion' => "Creó el equipo {$equipo->nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $equipo->load('propietario'), 'message' => 'Equipo creado'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear equipo'], 500);
        }
    }

    public function show($id)
    {
        $equipo = Equipo::with(['propietario', 'miembros', 'espaciosTrabajo'])->findOrFail($id);
        $this->authorizeAccess($equipo);
        return response()->json(['success' => true, 'data' => $equipo]);
    }

    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $this->authorizeAccess($equipo);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'sometimes|string|in:activo,inactivo',
        ]);

        $equipo->update($validated);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'objeto_type' => Equipo::class,
            'objeto_id' => $equipo->id,
            'descripcion' => "Editó el equipo {$equipo->nombre}",
        ]);

        return response()->json(['success' => true, 'data' => $equipo, 'message' => 'Equipo actualizado']);
    }

    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);
        if ($equipo->propietario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        DB::beginTransaction();
        try {
            $nombre = $equipo->nombre;
            $equipo->delete();

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => 'eliminar',
                'objeto_type' => Equipo::class,
                'objeto_id' => $id,
                'descripcion' => "Eliminó el equipo {$nombre}",
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Equipo eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar equipo'], 500);
        }
    }

    public function miembros($id)
    {
        $equipo = Equipo::findOrFail($id);
        $this->authorizeAccess($equipo);
        $miembros = MiembroEquipo::with('usuario')->where('equipo_id', $id)->get();
        return response()->json(['success' => true, 'data' => $miembros]);
    }

    public function agregarMiembro(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        if ($equipo->propietario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Solo el propietario puede agregar miembros'], 403);
        }

        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol' => 'sometimes|string|in:Owner,Administrador,Editor,Miembro,Invitado',
        ]);

        $existe = MiembroEquipo::where('equipo_id', $id)->where('usuario_id', $validated['usuario_id'])->exists();
        if ($existe) {
            return response()->json(['success' => false, 'message' => 'El usuario ya es miembro'], 409);
        }

        $miembro = MiembroEquipo::create([
            'equipo_id' => $id,
            'usuario_id' => $validated['usuario_id'],
            'rol' => $validated['rol'] ?? 'Miembro',
            'fecha_ingreso' => now(),
        ]);

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'crear',
            'objeto_type' => MiembroEquipo::class,
            'objeto_id' => $miembro->id,
            'descripcion' => "Agregó miembro al equipo {$equipo->nombre}",
        ]);

        $usuarioAgregado = User::find($validated['usuario_id']);
        $urlEquipo = NotificacionService::urlFrontend("equipos/{$equipo->id}");
        NotificacionService::notificar(
            $validated['usuario_id'],
            'Nuevo equipo',
            "Te han agregado al equipo {$equipo->nombre} con el rol de {$miembro->rol}",
            'sistema',
            $urlEquipo,
            $usuarioAgregado ? new NotificacionEquipoMail($usuarioAgregado, [
                'asunto' => "Te agregaron al equipo {$equipo->nombre}",
                'equipo' => ['nombre' => $equipo->nombre],
                'rol' => $miembro->rol,
                'url' => $urlEquipo,
            ]) : null
        );

        return response()->json(['success' => true, 'data' => $miembro->load('usuario'), 'message' => 'Miembro agregado'], 201);
    }

    public function eliminarMiembro($id, $usuarioId)
    {
        $equipo = Equipo::findOrFail($id);
        if ($equipo->propietario_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $miembro = MiembroEquipo::where('equipo_id', $id)->where('usuario_id', $usuarioId)->firstOrFail();
        $usuarioEliminado = User::find($usuarioId);
        $miembro->delete();

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'eliminar',
            'objeto_type' => MiembroEquipo::class,
            'objeto_id' => $miembro->id,
            'descripcion' => "Eliminó miembro del equipo {$equipo->nombre}",
        ]);

        NotificacionService::notificar(
            $usuarioId,
            'Miembro retirado',
            "Has sido eliminado del equipo {$equipo->nombre}",
            'sistema',
            null,
            $usuarioEliminado ? new NotificacionSistemaMail($usuarioEliminado, [
                'asunto' => "Fuiste retirado del equipo {$equipo->nombre}",
                'mensaje' => "Has sido eliminado del equipo {$equipo->nombre}. Ya no tienes acceso a sus espacios de trabajo.",
            ]) : null
        );

        return response()->json(['success' => true, 'message' => 'Miembro eliminado']);
    }

    private function authorizeAccess($equipo)
    {
        $member = $equipo->miembros()->where('usuario_id', Auth::id())->exists();
        if ($equipo->propietario_id !== Auth::id() && !$member) {
            abort(403, 'No autorizado');
        }
    }
}
