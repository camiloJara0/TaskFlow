<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\EspacioTrabajoController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\TablaController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\SubtareaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\ReaccionController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\RecordatorioController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\AutomatizacionController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\TokenPushController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\MiembroEquipoController;
use App\Http\Controllers\GamificacionController;
use App\Http\Controllers\PreferenciaController;
use App\Http\Controllers\ReunionController;

// Rutas públicas (con throttle)
Route::post('/v1/register', [UserController::class, 'register'])->middleware('throttle:5,1');
Route::post('/v1/login', [UserController::class, 'login'])->middleware('throttle:10,1');
Route::post('/v1/enviar-codigo', [UserController::class, 'enviarCodigo'])->middleware('throttle:3,5');
Route::post('/v1/verificar-codigo-cambio', [UserController::class, 'verificarCodigoCambio'])->middleware('throttle:5,1');

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Perfil / Auth
    Route::get('/v1/perfil', [UserController::class, 'perfil']);
    Route::put('/v1/perfil', [UserController::class, 'actualizarPerfil']);
    Route::post('/v1/logout', [UserController::class, 'logout']);

    // Equipos
    Route::get('/v1/perfiles', [UserController::class, 'usuarios']);
    Route::get('/v1/equipos', [EquipoController::class, 'index']);
    Route::post('/v1/equipos', [EquipoController::class, 'store']);
    Route::get('/v1/equipos/{equipo}', [EquipoController::class, 'show']);
    Route::put('/v1/equipos/{equipo}', [EquipoController::class, 'update']);
    Route::delete('/v1/equipos/{equipo}', [EquipoController::class, 'destroy']);
    Route::get('/v1/equipos/{equipo}/miembros', [EquipoController::class, 'miembros']);
    Route::post('/v1/equipos/{equipo}/miembros', [EquipoController::class, 'agregarMiembro']);
    Route::delete('/v1/equipos/{equipo}/miembros/{usuario}', [EquipoController::class, 'eliminarMiembro']);

    // Miembros de equipo gestión de roles
    Route::get('/v1/equipos/{equipo}/miembros-rol', [MiembroEquipoController::class, 'index']);
    Route::put('/v1/miembros-equipo/{miembro}', [MiembroEquipoController::class, 'update']);
    Route::delete('/v1/miembros-equipo/{miembro}', [MiembroEquipoController::class, 'destroy']);

    // Espacios de trabajo
    Route::get('/v1/espacios-trabajo', [EspacioTrabajoController::class, 'index']);
    Route::post('/v1/espacios-trabajo', [EspacioTrabajoController::class, 'store']);
    Route::get('/v1/espacios-trabajo/{espacio}', [EspacioTrabajoController::class, 'show']);
    Route::put('/v1/espacios-trabajo/{espacio}', [EspacioTrabajoController::class, 'update']);
    Route::delete('/v1/espacios-trabajo/{espacio}', [EspacioTrabajoController::class, 'destroy']);

    // Estados
    Route::get('/v1/estados', [EstadoController::class, 'index']);
    Route::post('/v1/estados', [EstadoController::class, 'store']);
    Route::get('/v1/estados/{estado}', [EstadoController::class, 'show']);
    Route::put('/v1/estados/{estado}', [EstadoController::class, 'update']);
    Route::delete('/v1/estados/{estado}', [EstadoController::class, 'destroy']);
    Route::post('/v1/estados/reordenar', [EstadoController::class, 'reordenar']);

    // Tablas
    Route::get('/v1/tablas', [TablaController::class, 'index']);
    Route::post('/v1/tablas', [TablaController::class, 'store']);
    Route::get('/v1/tablas/{tabla}', [TablaController::class, 'show']);
    Route::put('/v1/tablas/{tabla}', [TablaController::class, 'update']);
    Route::delete('/v1/tablas/{tabla}', [TablaController::class, 'destroy']);
    Route::post('/v1/tablas/reordenar', [TablaController::class, 'reordenar']);

    // Etiquetas
    Route::get('/v1/etiquetas', [EtiquetaController::class, 'index']);
    Route::post('/v1/etiquetas', [EtiquetaController::class, 'store']);
    Route::get('/v1/etiquetas/{etiqueta}', [EtiquetaController::class, 'show']);
    Route::put('/v1/etiquetas/{etiqueta}', [EtiquetaController::class, 'update']);
    Route::delete('/v1/etiquetas/{etiqueta}', [EtiquetaController::class, 'destroy']);

    // Tareas
    Route::get('/v1/tareas', [TareaController::class, 'index']);
    Route::post('/v1/tareas', [TareaController::class, 'store']);
    Route::get('/v1/tareas/{tarea}', [TareaController::class, 'show']);
    Route::get('/v1/workspaces/{workspaces}/tareas', [TareaController::class, 'porWorkspace']);
    Route::put('/v1/tareas/{tarea}', [TareaController::class, 'update']);
    Route::delete('/v1/tareas/{tarea}', [TareaController::class, 'destroy']);
    Route::put('/v1/tareas/{tarea}/cambiar-estado', [TareaController::class, 'cambiarEstado']);
    Route::put('/v1/tareas/{tarea}/asignar-responsable', [TareaController::class, 'asignarResponsable']);
    Route::post('/v1/tareas/reordenar', [TareaController::class, 'reordenar']);

    // Subtareas
    Route::get('/v1/tareas/{tarea}/subtareas', [SubtareaController::class, 'index']);
    Route::post('/v1/tareas/{tarea}/subtareas', [SubtareaController::class, 'store']);
    Route::put('/v1/subtareas/{subtarea}', [SubtareaController::class, 'update']);
    Route::delete('/v1/subtareas/{subtarea}', [SubtareaController::class, 'destroy']);
    Route::put('/v1/subtareas/{subtarea}/cambiar-estado', [SubtareaController::class, 'cambiarEstado']);

    // Comentarios
    Route::get('/v1/tareas/{tarea}/comentarios', [ComentarioController::class, 'index']);
    Route::post('/v1/tareas/{tarea}/comentarios', [ComentarioController::class, 'store']);
    Route::put('/v1/comentarios/{comentario}', [ComentarioController::class, 'update']);
    Route::delete('/v1/comentarios/{comentario}', [ComentarioController::class, 'destroy']);

    // Reacciones
    Route::post('/v1/reacciones', [ReaccionController::class, 'store']);
    Route::delete('/v1/reacciones/{reaccion}', [ReaccionController::class, 'destroy']);

    // Dependencias
    Route::get('/v1/tareas/{tarea}/dependencias', [DependenciaController::class, 'index']);
    Route::post('/v1/tareas/{tarea}/dependencias', [DependenciaController::class, 'store']);
    Route::delete('/v1/dependencias/{dependencia}', [DependenciaController::class, 'destroy']);

    // Archivos
    Route::get('/v1/tareas/{tarea}/archivos', [ArchivoController::class, 'index']);
    Route::post('/v1/tareas/{tarea}/archivos', [ArchivoController::class, 'store']);
    Route::get('/v1/archivos/{archivo}', [ArchivoController::class, 'show']);
    Route::delete('/v1/archivos/{archivo}', [ArchivoController::class, 'destroy']);
    Route::get('/v1/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar']);

    // Checklists
    Route::get('/v1/tareas/{tarea}/checklists', [ChecklistController::class, 'index']);
    Route::post('/v1/tareas/{tarea}/checklists', [ChecklistController::class, 'store']);
    Route::put('/v1/checklists/{checklist}', [ChecklistController::class, 'update']);
    Route::delete('/v1/checklists/{checklist}', [ChecklistController::class, 'destroy']);
    Route::put('/v1/checklists/{checklist}/cambiar-estado', [ChecklistController::class, 'cambiarEstado']);
    Route::post('/v1/checklists/reordenar', [ChecklistController::class, 'reordenar']);

    // Notificaciones
    Route::get('/v1/notificaciones', [NotificacionController::class, 'index']);
    Route::put('/v1/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida']);
    Route::put('/v1/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
    Route::delete('/v1/notificaciones/{notificacion}', [NotificacionController::class, 'destroy']);

    // Recordatorios
    Route::get('/v1/recordatorios', [RecordatorioController::class, 'index']);
    Route::post('/v1/recordatorios', [RecordatorioController::class, 'store']);
    Route::put('/v1/recordatorios/{recordatorio}', [RecordatorioController::class, 'update']);
    Route::delete('/v1/recordatorios/{recordatorio}', [RecordatorioController::class, 'destroy']);

    // Actividad
    Route::get('/v1/actividades', [ActividadController::class, 'index']);
    Route::get('/v1/actividades/{actividad}', [ActividadController::class, 'show']);
    Route::get('/v1/tareas/{tarea}/actividades', [ActividadController::class, 'index']);

    // Favoritos
    Route::get('/v1/favoritos', [FavoritoController::class, 'index']);
    Route::post('/v1/favoritos', [FavoritoController::class, 'store']);
    Route::delete('/v1/favoritos/{favorito}', [FavoritoController::class, 'destroy']);

    // Automatizaciones
    Route::get('/v1/workspaces/{workspace}/automatizaciones', [AutomatizacionController::class, 'index']);
    Route::post('/v1/workspaces/{workspace}/automatizaciones', [AutomatizacionController::class, 'store']);
    Route::put('/v1/automatizaciones/{automatizacion}', [AutomatizacionController::class, 'update']);
    Route::delete('/v1/automatizaciones/{automatizacion}', [AutomatizacionController::class, 'destroy']);
    Route::put('/v1/automatizaciones/{automatizacion}/toggle', [AutomatizacionController::class, 'toggleActivo']);

    // Plantillas
    Route::get('/v1/workspaces/{workspace}/plantillas', [PlantillaController::class, 'index']);
    Route::post('/v1/workspaces/{workspace}/plantillas', [PlantillaController::class, 'store']);
    Route::get('/v1/plantillas/{plantilla}', [PlantillaController::class, 'show']);
    Route::delete('/v1/plantillas/{plantilla}', [PlantillaController::class, 'destroy']);
    Route::post('/v1/plantillas/{plantilla}/aplicar', [PlantillaController::class, 'aplicar']);

    // Reuniones
    Route::get('/v1/reuniones', [ReunionController::class, 'index']);
    Route::post('/v1/reuniones', [ReunionController::class, 'store']);
    Route::get('/v1/reuniones/{reunion}', [ReunionController::class, 'show']);
    Route::put('/v1/reuniones/{reunion}', [ReunionController::class, 'update']);
    Route::delete('/v1/reuniones/{reunion}', [ReunionController::class, 'destroy']);
    Route::put('/v1/reuniones/{reunion}/cambiar-estado', [ReunionController::class, 'cambiarEstado']);
    Route::put('/v1/reuniones/{reunion}/archivar', [ReunionController::class, 'toggleArchivar']);

    // Push tokens
    Route::post('/v1/push-tokens', [TokenPushController::class, 'store']);
    Route::delete('/v1/push-tokens/{token}', [TokenPushController::class, 'destroy'])->where('token', '.*');

    // Auditoría
    Route::get('/v1/auditoria', [AuditoriaController::class, 'index']);
    Route::get('/v1/auditoria/{auditoria}', [AuditoriaController::class, 'show']);

    // Gamificación
    Route::get('/v1/gamificacion', [GamificacionController::class, 'index']);
    Route::post('/v1/gamificacion/tareas-completadas', [GamificacionController::class, 'tareasCompletadas']);
    Route::put('/v1/gamificacion/sonidos', [GamificacionController::class, 'sonidos']);
    Route::delete('/v1/gamificacion', [GamificacionController::class, 'destroy']);
    Route::get('/v1/logros', [GamificacionController::class, 'logros']);

    // Apariencia
    Route::get('/v1/perfil/apariencia', [PreferenciaController::class, 'index']);
    Route::put('/v1/perfil/apariencia', [PreferenciaController::class, 'update']);
});