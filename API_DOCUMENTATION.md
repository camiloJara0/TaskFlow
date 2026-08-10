# Documentación de la API - Sistema de Gestión de Tareas

Base URL: `http://localhost:8000/api`

Autenticación: `Authorization: Bearer {token}` (Sanctum)

---

## Índice

1. [Autenticación](#1-autenticación)
2. [Perfil de Usuario](#2-perfil-de-usuario)
3. [Equipos](#3-equipos)
4. [Miembros de Equipo](#4-miembros-de-equipo)
5. [Espacios de Trabajo](#5-espacios-de-trabajo)
6. [Proyectos](#6-proyectos)
7. [Estados](#7-estados)
8. [Tablas (Lists/Boards)](#8-tablas)
9. [Etiquetas](#9-etiquetas)
10. [Tareas](#10-tareas)
11. [Subtareas](#11-subtareas)
12. [Comentarios](#12-comentarios)
13. [Reacciones](#13-reacciones)
14. [Dependencias](#14-dependencias)
15. [Archivos](#15-archivos)
16. [Checklists](#16-checklists)
17. [Notificaciones](#17-notificaciones)
18. [Recordatorios](#18-recordatorios)
19. [Actividad](#19-actividad)
20. [Favoritos](#20-favoritos)
21. [Automatizaciones](#21-automatizaciones)
22. [Plantillas](#22-plantillas)
23. [Tokens Push](#23-tokens-push)
24. [Auditoría](#24-auditoría)

---

## 1. Autenticación

### POST /api/v1/register
Registro de nuevo usuario.

**Throttle:** 5 por minuto

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | sí | Nombre completo, máx 255 |
| `email` | string | sí | Email válido, único en el sistema |
| `password` | string | sí | Mínimo 8 caracteres |
| `password_confirmation` | string | sí | Debe coincidir con password |

**Respuesta 201:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": { "id": 1, "nombre": "...", "email": "...", "zona_horaria": "UTC", "idioma": "es", "tema": "claro" }
  }
}
```

---

### POST /api/v1/login
Inicio de sesión.

**Throttle:** 10 por minuto

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `email` | string | sí | Email registrado |
| `password` | string | sí | Contraseña |

**Respuesta 200:**
```json
{
  "success": true,
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "expires_in": 57600,
  "data": {
    "user": { "id": 1, "nombre": "...", "email": "...", "foto": null, "estado": "activo", "zona_horaria": "UTC", "idioma": "es", "tema": "claro" }
  }
}
```

**Errores:**
- 403: `USER_NOT_FOUND` / `USER_INACTIVE` / `INVALID_PASSWORD`

---

### POST /api/v1/enviar-codigo
Envía código de verificación de 6 dígitos al correo (válido 15 min).

**Throttle:** 3 cada 5 minutos

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `email` | string | sí | Email registrado en el sistema |

**Respuesta 200:**
```json
{ "success": true, "message": "Código de verificación enviado al correo" }
```

---

### POST /api/v1/verificar-codigo-cambio
Verifica el código y cambia la contraseña.

**Throttle:** 5 por minuto

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `email` | string | sí | Email registrado |
| `codigo` | string | sí | Código de 6 dígitos recibido |
| `password` | string | sí | Nueva contraseña, mín 8 caracteres |
| `password_confirmation` | string | sí | Confirmación de contraseña |

**Respuesta 200:**
```json
{ "success": true, "message": "Contraseña actualizada correctamente" }
```

---

## 2. Perfil de Usuario

Todas requieren `Authorization: Bearer {token}`

### GET /api/v1/perfil
Obtiene datos del usuario autenticado.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 1, "nombre": "...", "email": "...", "foto": null,
    "estado": "activo", "ultimo_login": "2026-07-27T10:00:00Z",
    "zona_horaria": "UTC", "idioma": "es", "tema": "claro"
  }
}
```

---

### PUT /api/v1/perfil
Actualiza datos del perfil.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | no | Nombre, máx 255 |
| `foto` | string | no | URL de la foto, nullable |
| `zona_horaria` | string | no | Ej: "America/Bogota", máx 50 |
| `idioma` | string | no | Ej: "es", "en", máx 10 |
| `tema` | string | no | `claro` o `oscuro` |

**Respuesta:** misma estructura que GET /perfil

---

### POST /api/v1/logout
Cierra sesión y revoca el token actual.

**Respuesta:**
```json
{ "success": true, "message": "Sesión cerrada exitosamente" }
```

---

## 3. Equipos

### GET /api/v1/equipos
Lista equipos donde el usuario es propietario o miembro.

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1, "nombre": "Mi Equipo", "descripcion": "...", "icono": "...",
      "color": "#ff0000", "propietario_id": 1, "estado": "activo",
      "propietario": { "id": 1, "nombre": "..." },
      "miembros": [ { "id": 1, "nombre": "..." } ]
    }
  ]
}
```

---

### POST /api/v1/equipos
Crear equipo.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | sí | Máx 255 |
| `descripcion` | string | no | Texto libre |
| `icono` | string | no | Máx 50 |
| `color` | string | no | Máx 20 (hex, nombre, etc) |

**Respuesta 201:** datos del equipo con propietario

---

### GET /api/v1/equipos/{equipo}
Muestra un equipo con propietario, miembros y espacios de trabajo.

---

### PUT /api/v1/equipos/{equipo}
Actualiza equipo.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | no | Máx 255 |
| `descripcion` | string | no | Texto libre |
| `icono` | string | no | Máx 50 |
| `color` | string | no | Máx 20 |
| `estado` | string | no | `activo` o `inactivo` |

---

### DELETE /api/v1/equipos/{equipo}
Elimina equipo (solo propietario).

---

### GET /api/v1/equipos/{equipo}/miembros
Lista miembros del equipo con datos de usuario.

---

### POST /api/v1/equipos/{equipo}/miembros
Agrega miembro al equipo (solo propietario).

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `usuario_id` | integer | sí | ID del usuario |
| `rol` | string | no | `Owner`, `Administrador`, `Editor`, `Miembro`, `Invitado` |

**Respuesta 201:** datos del miembro con usuario

---

### DELETE /api/v1/equipos/{equipo}/miembros/{usuario}
Elimina miembro del equipo (solo propietario).

---

## 4. Miembros de Equipo

### GET /api/v1/equipos/{equipo}/miembros-rol
Lista miembros con sus roles.

---

### PUT /api/v1/miembros-equipo/{miembro}
Actualiza rol y permisos de un miembro.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `rol` | string | sí | `Owner`, `Administrador`, `Editor`, `Miembro`, `Invitado` |
| `permisos` | array | no | Array de permisos personalizados |

---

### DELETE /api/v1/miembros-equipo/{miembro}
Elimina miembro del equipo.

---

## 5. Espacios de Trabajo

### GET /api/v1/espacios-trabajo
Lista espacios de trabajo ordenados por `orden`.

---

### POST /api/v1/espacios-trabajo

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `equipo_id` | integer | sí | ID del equipo |
| `nombre` | string | sí | Máx 255 |
| `descripcion` | string | no | Texto libre |
| `color` | string | no | Máx 20 |
| `icono` | string | no | Máx 50 |
| `orden` | integer | no | Para ordenar |

---

### GET /api/v1/espacios-trabajo/{espacio}
Muestra con equipo, proyectos, estados, tablas y etiquetas.

---

### PUT /api/v1/espacios-trabajo/{espacio}

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `nombre` | string | no |
| `descripcion` | string | no |
| `color` | string | no |
| `icono` | string | no |
| `orden` | integer | no |

---

### DELETE /api/v1/espacios-trabajo/{espacio}

---

## 6. Proyectos

### GET /api/v1/proyectos
Lista proyectos con workspace y responsable.

---

### POST /api/v1/proyectos

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `workspace_id` | integer | sí | Debe existir en espacios_trabajo |
| `nombre` | string | sí | Máx 255 |
| `descripcion` | string | no | Texto libre |
| `color` | string | no | Máx 20 |
| `fecha_inicio` | date | no | Formato YYYY-MM-DD |
| `fecha_fin` | date | no | Debe ser >= fecha_inicio |
| `responsable_id` | integer | no | Debe existir en users |

---

### GET /api/v1/proyectos/{proyecto}
Muestra con workspace, responsable, tareas con estado y responsable.

---

### PUT /api/v1/proyectos/{proyecto}

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `nombre` | string | no |
| `descripcion` | string | no |
| `estado` | string | no |
| `color` | string | no |
| `fecha_inicio` | date | no |
| `fecha_fin` | date | no |
| `responsable_id` | integer | no |

---

### DELETE /api/v1/proyectos/{proyecto}

---

## 7. Estados

### GET /api/v1/estados
Lista estados ordenados por `orden`.

---

### POST /api/v1/estados

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `workspace_id` | integer | sí |
| `nombre` | string | sí, máx 255 |
| `color` | string | no, máx 20 |
| `orden` | integer | no |

---

### GET /api/v1/estados/{estado}
Muestra con tareas asociadas.

---

### PUT /api/v1/estados/{estado}

| Campo | Tipo |
|-------|------|
| `nombre` | string, no |
| `color` | string, no |
| `orden` | integer, no |

---

### DELETE /api/v1/estados/{estado}

---

### POST /api/v1/estados/reordenar
Reordena estados.

```json
{
  "orden": [
    { "id": 1, "orden": 0 },
    { "id": 2, "orden": 1 }
  ]
}
```

---

## 8. Tablas

### GET /api/v1/tablas

---

### POST /api/v1/tablas

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `workspace_id` | integer | sí |
| `nombre` | string | sí, máx 255 |
| `color` | string | no, máx 20 |
| `orden` | integer | no |

---

### GET /api/v1/tablas/{tabla}
Muestra con tareas (con estado y responsable).

---

### PUT /api/v1/tablas/{tabla}

| Campo | Tipo |
|-------|------|
| `nombre` | string, no |
| `color` | string, no |
| `orden` | integer, no |

---

### DELETE /api/v1/tablas/{tabla}

---

### POST /api/v1/tablas/reordenar

```json
{
  "orden": [
    { "id": 1, "orden": 0 },
    { "id": 2, "orden": 1 }
  ]
}
```

---

## 9. Etiquetas

### GET /api/v1/etiquetas
Lista con workspace.

---

### POST /api/v1/etiquetas

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `workspace_id` | integer | sí |
| `nombre` | string | sí, máx 255 |
| `color` | string | no, máx 20 |
| `icono` | string | no, máx 50 |

---

### GET /api/v1/etiquetas/{etiqueta}

---

### PUT /api/v1/etiquetas/{etiqueta}

| Campo | Tipo |
|-------|------|
| `nombre` | string, no |
| `color` | string, no |
| `icono` | string, no |

---

### DELETE /api/v1/etiquetas/{etiqueta}

---

## 10. Tareas

### GET /api/v1/tareas
Lista tareas no archivadas con proyecto, lista, creador, responsable, estado, etiquetas y subtareas.

---

### POST /api/v1/tareas

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `lista_id` | integer | **sí** | Debe existir en tablas |
| `titulo` | string | **sí** | Máx 500 |
| `estado_id` | integer | **sí** | Debe existir en estados |
| `proyecto_id` | integer | no | Existencia en proyectos |
| `responsable_id` | integer | no | Existencia en users |
| `descripcion` | string | no | Texto libre |
| `prioridad` | string | no | `Alta`, `Media`, `Baja`, `Urgente` |
| `fecha_inicio` | datetime | no | Formato ISO |
| `fecha_vencimiento` | datetime | no | Formato ISO |
| `estimacion_horas` | numeric | no | Mín 0 |
| `orden` | integer | no | |
| `es_recurrente` | boolean | no | |
| `etiquetas` | array | no | Array de IDs de etiquetas |

**Respuesta 201:** tarea con estado, etiquetas y responsable

---

### GET /api/v1/tareas/{tarea}
Muestra con: proyecto, lista, creador, responsable, estado, etiquetas, subtareas, comentarios (con usuario y reacciones), archivos, checklists, dependencias (padre e hija), recordatorios.

---

### PUT /api/v1/tareas/{tarea}

| Campo | Tipo | Validación |
|-------|------|------------|
| `titulo` | string, no | Máx 500 |
| `lista_id` | integer, no | Existencia en tablas |
| `estado_id` | integer, no | Existencia en estados |
| `proyecto_id` | integer, no | Existencia en proyectos |
| `responsable_id` | integer, no | Nullable, existencia en users |
| `descripcion` | string, no | |
| `prioridad` | string, no | `Alta`, `Media`, `Baja`, `Urgente` |
| `fecha_inicio` | datetime, no | |
| `fecha_vencimiento` | datetime, no | |
| `estimacion_horas` | numeric, no | Mín 0 |
| `horas_invertidas` | numeric, no | Mín 0 |
| `porcentaje` | integer, no | 0-100 |
| `orden` | integer, no | |
| `es_recurrente` | boolean, no | |
| `archivada` | boolean, no | |
| `etiquetas` | array, no | Array de IDs |

---

### DELETE /api/v1/tareas/{tarea}

---

### PUT /api/v1/tareas/{tarea}/cambiar-estado
Cambia solo el estado de la tarea.

```json
{ "estado_id": 5 }
```

---

### PUT /api/v1/tareas/{tarea}/asignar-responsable
Asigna o cambia el responsable.

```json
{ "responsable_id": 3 }
```
Enviar `{ "responsable_id": null }` para desasignar.

---

### POST /api/v1/tareas/reordenar
Reordena tareas y opcionalmente las mueve entre listas.

```json
{
  "orden": [
    { "id": 1, "orden": 0, "lista_id": 2 },
    { "id": 2, "orden": 1, "lista_id": 2 }
  ]
}
```

---

## 11. Subtareas

### GET /api/v1/tareas/{tarea}/subtareas
Lista subtareas de una tarea, ordenadas por `orden`.

---

### POST /api/v1/tareas/{tarea}/subtareas

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `titulo` | string | sí, máx 500 |
| `orden` | integer | no |

---

### PUT /api/v1/subtareas/{subtarea}

| Campo | Tipo |
|-------|------|
| `titulo` | string, no |
| `orden` | integer, no |

---

### DELETE /api/v1/subtareas/{subtarea}

---

### PUT /api/v1/subtareas/{subtarea}/cambiar-estado

```json
{ "estado": true }
```

---

## 12. Comentarios

### GET /api/v1/tareas/{tarea}/comentarios
Lista comentarios con usuario y reacciones (con usuario).

---

### POST /api/v1/tareas/{tarea}/comentarios

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `comentario` | string | sí |

---

### PUT /api/v1/comentarios/{comentario}
Solo el autor del comentario puede editarlo.

| Campo | Tipo |
|-------|------|
| `comentario` | string, sí |

---

### DELETE /api/v1/comentarios/{comentario}
Solo el autor puede eliminar.

---

## 13. Reacciones

### POST /api/v1/reacciones

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `comentario_id` | integer | sí | ID del comentario |
| `tipo` | string | sí | `👍`, `❤️`, `😂`, `👏` |

**Nota:** No permite duplicados (mismo usuario + comentario + tipo).

---

### DELETE /api/v1/reacciones/{reaccion}
Solo el autor puede eliminar.

---

## 14. Dependencias

### GET /api/v1/tareas/{tarea}/dependencias
Lista dependencias donde la tarea es padre o hija.

---

### POST /api/v1/tareas/{tarea}/dependencias
Crea dependencia. La `{tarea}` de la URL es la tarea padre.

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `tarea_hija_id` | integer | sí | Diferente a tarea padre |
| `tipo` | string | no | `fin_a_inicio`, `inicio_a_inicio`, `fin_a_fin` |

---

### DELETE /api/v1/dependencias/{dependencia}

---

## 15. Archivos

### GET /api/v1/tareas/{tarea}/archivos

---

### POST /api/v1/tareas/{tarea}/archivos
Sube archivo (multipart/form-data). Máx 100MB.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `archivo` | file | sí | Archivo a subir |
| `nombre` | string | no | Nombre personalizado |

---

### GET /api/v1/archivos/{archivo}

---

### GET /api/v1/archivos/{archivo}/descargar
Descarga el archivo.

---

### DELETE /api/v1/archivos/{archivo}
Elimina archivo del storage y BD.

---

## 16. Checklists

### GET /api/v1/tareas/{tarea}/checklists

---

### POST /api/v1/tareas/{tarea}/checklists

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `titulo` | string | sí, máx 500 |
| `orden` | integer | no |

---

### PUT /api/v1/checklists/{checklist}

| Campo | Tipo |
|-------|------|
| `titulo` | string, no |
| `orden` | integer, no |

---

### DELETE /api/v1/checklists/{checklist}

---

### PUT /api/v1/checklists/{checklist}/cambiar-estado

```json
{ "estado": true }
```

---

### POST /api/v1/checklists/reordenar

```json
{
  "orden": [
    { "id": 1, "orden": 0 },
    { "id": 2, "orden": 1 }
  ]
}
```

---

## 17. Notificaciones

### GET /api/v1/notificaciones
Lista notificaciones del usuario autenticado.

```json
{
  "success": true,
  "data": [
    {
      "id": 1, "usuario_id": 1, "titulo": "...", "mensaje": "...",
      "tipo": "comentario", "url": "...", "leida": false,
      "created_at": "..."
    }
  ]
}
```

**Tipos:** `comentario`, `asignacion`, `recordatorio`, `cambio_estado`, `mencion`, `sistema`

---

### PUT /api/v1/notificaciones/{notificacion}/leer
Marca como leída.

---

### PUT /api/v1/notificaciones/leer-todas
Marca todas como leídas.

---

### DELETE /api/v1/notificaciones/{notificacion}

---

## 18. Recordatorios

### GET /api/v1/recordatorios
Lista recordatorios del usuario, ordenados por fecha.

---

### POST /api/v1/recordatorios

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `tarea_id` | integer | sí | Tarea asociada |
| `fecha` | datetime | sí | Fecha del recordatorio |
| `tipo` | string | no | `5_minutos`, `30_minutos`, `1_hora`, `1_dia`, `1_semana` |

---

### PUT /api/v1/recordatorios/{recordatorio}

| Campo | Tipo |
|-------|------|
| `fecha` | datetime, no |
| `tipo` | string, no |

---

### DELETE /api/v1/recordatorios/{recordatorio}

---

## 19. Actividad

### GET /api/v1/actividades
Feed de actividad global (últimas 100).

---

### GET /api/v1/tareas/{tarea}/actividades
Actividad filtrada por tarea específica.

---

### GET /api/v1/actividades/{actividad}
Detalle de una actividad.

```json
{
  "success": true,
  "data": {
    "id": 1, "usuario_id": 1, "tipo": "cambio_estado",
    "objeto_type": "App\\Models\\Tarea", "objeto_id": 1,
    "descripcion": "Cambió estado de 'Pendiente' a 'Completada'",
    "usuario": { "id": 1, "nombre": "Juan" }
  }
}
```

---

## 20. Favoritos

### GET /api/v1/favoritos
Lista favoritos del usuario con el objeto favorito (polimórfico).

---

### POST /api/v1/favoritos

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `favoritable_type` | string | sí | Nombre de clase: `App\\Models\\Proyecto`, `App\\Models\\Tarea`, etc |
| `favoritable_id` | integer | sí | ID del objeto |

---

### DELETE /api/v1/favoritos/{favorito}

---

## 21. Automatizaciones

### GET /api/v1/workspaces/{workspace}/automatizaciones
Lista automatizaciones de un workspace.

---

### POST /api/v1/workspaces/{workspace}/automatizaciones

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | sí | Máx 255 |
| `evento` | string | sí | Ej: `estado_cambia`, `tarea_creada`, máx 100 |
| `condicion` | object | no | JSON con condiciones |
| `accion` | object | sí | JSON con la acción a ejecutar |

---

### PUT /api/v1/automatizaciones/{automatizacion}

| Campo | Tipo |
|-------|------|
| `nombre` | string, no |
| `evento` | string, no |
| `condicion` | object, no |
| `accion` | object, no |

---

### DELETE /api/v1/automatizaciones/{automatizacion}

---

### PUT /api/v1/automatizaciones/{automatizacion}/toggle
Activa/desactiva una automatización (no requiere body).

---

## 22. Plantillas

### GET /api/v1/workspaces/{workspace}/plantillas
Lista plantillas de un workspace.

---

### POST /api/v1/workspaces/{workspace}/plantillas

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre` | string | sí | Máx 255 |
| `descripcion` | string | no | |
| `datos` | object | sí | JSON con estructura del proyecto/tareas |

---

### GET /api/v1/plantillas/{plantilla}

---

### DELETE /api/v1/plantillas/{plantilla}

---

### POST /api/v1/plantillas/{plantilla}/aplicar
Aplica una plantilla creando un proyecto con sus tareas.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `workspace_id` | integer | sí | Workspace destino |
| `proyecto_nombre` | string | sí | Nombre del nuevo proyecto |

---

## 23. Tokens Push

### POST /api/v1/push-tokens
Registra token para notificaciones push.

| Campo | Tipo | Requerido |
|-------|------|-----------|
| `token` | string | sí |
| `navegador` | string | no |

---

### DELETE /api/v1/push-tokens/{token}
Desactiva el token.

---

## 24. Auditoría

### GET /api/v1/auditoria
Listado paginado de auditoría (50 por página).

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "last_page": 5,
    "total": 250
  }
}
```

---

### GET /api/v1/auditoria/{auditoria}
Detalle de un registro de auditoría.
