# Plan de Integración — Sistema de Gamificación (Backend)

> Documento de trabajo: describe las migraciones, modelos, endpoints y lógica necesarios
> para persistir el sistema de gamificación en el backend Laravel.
> El frontend (`app/composables/useGamification.ts`) ya consume estos contratos.

---

## Índice

1. [Modelo de datos](#1-modelo-de-datos)
2. [Migraciones](#2-migraciones)
3. [Modelos Eloquent](#3-modelos-eloquent)
4. [Endpoints](#4-endpoints)
5. [Lógica de negocio](#5-lógica-de-negocio)
6. [Lógica de logros](#6-lógica-de-logros)
7. [Seeders](#7-seeders)
8. [Integración con notificaciones](#8-integración-con-notificaciones)
9. [Contrato de respuesta (Frontend)](#9-contrato-de-respuesta)

---

## 1. Modelo de datos

El sistema se compone de 5 tablas nuevas (sin modificar las existentes):

| Tabla                 | Propósito                                          |
| --------------------- | -------------------------------------------------- |
| `gamificaciones`      | Estado global por usuario (XP, nivel, racha, etc.) |
| `gamificacion_diaria` | Histórico diario de tareas y XP (hoy/semana/mes)   |
| `logros`              | Catálogo de logros (seeded)                        |
| `gamificacion_logros` | Pivot: logros desbloqueados por usuario            |
| `xp_eventos`          | Auditoría de cada ganancia de XP (anti-duplicados) |

**Reglas clave:**
- Un usuario tiene **1 fila** en `gamificaciones` (relación `1:1` con `users`).
- `xp_eventos` guarda `tarea_id` con índice único para **evitar doble recompensa**
  si el cliente reenvía el mismo evento.
- Los logros de racha/nivel se calculan **del lado del servidor** y se insertan
  en `gamificacion_logros`.

---

## 2. Migraciones

### 2.1 `create_gamificaciones_table`

```php
Schema::create('gamificaciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete()->unique();
    $table->unsignedBigInteger('xp')->default(0);
    $table->unsignedInteger('nivel')->default(1);
    $table->unsignedInteger('racha')->default(0);
    $table->unsignedInteger('mejor_racha')->default(0);
    $table->date('ultimo_dia_activo')->nullable();
    $table->unsignedBigInteger('tareas_completadas')->default(0);
    $table->json('preferencias')->nullable(); // { sonidos_activados, volumen }
    $table->timestamps();
});
```

### 2.2 `create_gamificacion_diaria_table`

```php
Schema::create('gamificacion_diaria', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
    $table->date('fecha');
    $table->unsignedInteger('tareas')->default(0);
    $table->unsignedBigInteger('xp')->default(0);
    $table->unique(['gamificacion_id', 'fecha']);
});
```

### 2.3 `create_logros_table`

```php
Schema::create('logros', function (Blueprint $table) {
    $table->id();
    $table->string('slug')->unique();          // 'primera-victoria'
    $table->string('titulo');
    $table->string('descripcion');
    $table->string('icono')->default('i-lucide-trophy');
    $table->enum('rareza', ['comun', 'raro', 'epico', 'legendario'])->default('comun');
    $table->enum('categoria', [
        'productividad', 'disciplina', 'constancia',
        'estudio', 'trabajo', 'organizacion'
    ])->default('productividad');
    $table->string('condicion')->nullable();   // descripción de la condición (hint)
    $table->unsignedInteger('meta')->nullable(); // valor objetivo (para progreso)
    $table->timestamps();
});
```

### 2.4 `create_gamificacion_logros_table` (pivot)

```php
Schema::create('gamificacion_logros', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
    $table->foreignId('logro_id')->constrained('logros')->cascadeOnDelete();
    $table->timestamp('desbloqueado_en')->useCurrent();
    $table->unique(['gamificacion_id', 'logro_id']);
});
```

### 2.5 `create_xp_eventos_table`

```php
Schema::create('xp_eventos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gamificacion_id')->constrained('gamificaciones')->cascadeOnDelete();
    $table->unsignedBigInteger('tarea_id')->nullable();
    $table->unsignedBigInteger('cantidad');
    $table->string('motivo');                  // 'tarea_completada' | 'logro' | 'hito_racha'
    $table->timestamps();

    $table->unique(['gamificacion_id', 'tarea_id'], 'xp_evt_tarea_unique');
});
```

> **Nota:** `xp_eventos.tarea_id` es `nullable` para eventos que no provienen de una tarea
> (ej. bonificación por hito de racha) y `unique` con `gamificacion_id` para idempotencia.

---

## 3. Modelos Eloquent

### `app/Models/Gamificacion.php`

```php
class Gamificacion extends Model
{
    protected $fillable = [
        'usuario_id', 'xp', 'nivel', 'racha', 'mejor_racha',
        'ultimo_dia_activo', 'tareas_completadas', 'preferencias',
    ];

    protected $casts = [
        'ultimo_dia_activo' => 'date',
        'preferencias'      => 'array',
    ];

    public function usuario()          { return $this->belongsTo(User::class, 'usuario_id'); }
    public function registrosDiarios() { return $this->hasMany(GamificacionDiaria::class); }
    public function logros()           { return $this->belongsToMany(Logro::class, 'gamificacion_logros')->withPivot('desbloqueado_en'); }
    public function xpEventos()        { return $this->hasMany(XpEvento::class); }
}
```

### `app/Models/GamificacionDiaria.php`

```php
class GamificacionDiaria extends Model
{
    protected $table = 'gamificacion_diaria';
    protected $fillable = ['gamificacion_id', 'fecha', 'tareas', 'xp'];
    protected $casts = ['fecha' => 'date'];
}
```

### `app/Models/Logro.php`

```php
class Logro extends Model
{
    protected $fillable = ['slug', 'titulo', 'descripcion', 'icono', 'rareza', 'categoria', 'condicion', 'meta'];
}
```

### `app/Models/XpEvento.php`

```php
class XpEvento extends Model
{
    protected $table = 'xp_eventos';
    protected $fillable = ['gamificacion_id', 'tarea_id', 'cantidad', 'motivo'];
}
```

---

## 4. Endpoints

Todas bajo `Route::middleware('auth:sanctum')` en `routes/api.php`, prefijo `/api/v1`.

| Método | URI                                        | Descripción                                            |
| ------ | ------------------------------------------ | ------------------------------------------------------ |
| GET    | `/api/v1/gamificacion`                     | Perfil de gamificación del usuario autenticado         |
| POST   | `/api/v1/gamificacion/tareas-completadas`  | Registra una tarea completada y devuelve el resultado  |
| PUT    | `/api/v1/gamificacion/sonidos`             | Actualiza preferencias de sonido                       |
| GET    | `/api/v1/logros`                           | Catálogo de logros con estado del usuario              |
| DELETE | `/api/v1/gamificacion`                     | Reinicia el progreso (opcional, útil en desarrollo)    |

### 4.1 `GET /api/v1/gamificacion`

Devuelve el estado actual del usuario. Si no existe registro, lo crea.

```json
{
  "success": true,
  "data": {
    "xp": 275,
    "nivel": 3,
    "racha": 4,
    "mejor_racha": 7,
    "ultimo_dia_activo": "2026-08-05",
    "tareas_completadas": 18,
    "tareas_completadas_ids": [1, 2, 3, 7, 12],
    "sonidos_activados": true,
    "volumen": 0.6,
    "diario": [
      { "fecha": "2026-08-05", "tareas": 4, "xp": 100 },
      { "fecha": "2026-08-04", "tareas": 2, "xp": 35 }
    ],
    "logros": [
      { "slug": "primera-victoria", "desbloqueado_en": "2026-08-04T10:00:00Z" }
    ]
  }
}
```

### 4.2 `POST /api/v1/gamificacion/tareas-completadas`

**Body:**

```json
{ "tarea_id": 42 }
```

**Lógica del controlador:**
1. Busca la tarea (debe pertenecer al usuario o a un equipo/espacio del usuario).
2. Si `xp_eventos` ya contiene `(gamificacion_id, tarea_id)` → devuelve el estado
   actual sin volver a otorgar XP (**idempotente**).
3. Calcula XP según la prioridad de la tarea (ver tabla en §5.1).
4. Actualiza racha, contadores diarios y XP/nivel.
5. Evalúa logros (§6) e inserta los nuevos en el pivot.
6. Si sube de nivel o alcanza un hito de racha, crea `xp_evento` adicional opcional.

**Respuesta 200:**

```json
{
  "success": true,
  "data": {
    "xp_ganado": 50,
    "subio_nivel": true,
    "nuevo_nivel": 4,
    "hito_racha": null,
    "logros_nuevos": [
      { "slug": "racha-semana", "titulo": "Constancia Semanal", "icono": "i-lucide-calendar-check", "rareza": "comun" }
    ],
    "perfil": { "...perfil completo igual que GET /gamificacion..." }
  }
}
```

> **Importante para el frontend:** el campo `perfil` reutiliza la misma estructura de
> `GET /gamificacion`, así el cliente solo hace *un* round-trip por tarea.

### 4.3 `PUT /api/v1/gamificacion/sonidos`

**Body:**

```json
{ "sonidos_activados": true, "volumen": 0.6 }
```

Guarda en `gamificaciones.preferencias`. Respuesta:

```json
{ "success": true, "data": null }
```

### 4.4 `GET /api/v1/logros`

Devuelve el catálogo con `desbloqueado` por usuario:

```json
{
  "success": true,
  "data": [
    {
      "slug": "primera-victoria",
      "titulo": "Primera Victoria",
      "descripcion": "Completa tu primera tarea.",
      "icono": "i-lucide-trophy",
      "rareza": "comun",
      "categoria": "productividad",
      "condicion": "Completa cualquier tarea",
      "meta": 1,
      "desbloqueado": true,
      "desbloqueado_en": "2026-08-04T10:00:00Z"
    }
  ]
}
```

### 4.5 `DELETE /api/v1/gamificacion`

Elimina `gamificaciones` (cascadea diario, pivot y eventos). Para desarrollo/demo.

---

## 5. Lógica de negocio

### 5.1 XP por prioridad de tarea

| Prioridad | XP  |
| --------- | --- |
| Baja      | 10  |
| Media     | 25  |
| Alta      | 50  |
| Urgente   | 100 |

```php
private const XP_POR_PRIORIDAD = [
    'urgente' => 100, 'urgent' => 100, 'critica' => 100,
    'alta'    => 50,  'high'   => 50,
    'media'   => 25,  'medium' => 25,
    'baja'    => 10,  'low'    => 10,
];

private function xpParaPrioridad(?string $prioridad): int
{
    $p = strtolower(trim((string) $prioridad));
    return self::XP_POR_PRIORIDAD[$p] ?? 25;
}
```

### 5.2 Niveles y curva de XP

La XP requerida para el **siguiente** nivel sigue la fórmula del frontend:

```php
private function xpParaNivel(int $nivel): int
{
    return (int) round(80 * pow($nivel, 1.5));
}
```

Al acumular XP:

```php
while ($gamificacion->xp >= $this->xpParaNivel($gamificacion->nivel) && $gamificacion->nivel < 100) {
    $gamificacion->xp -= $this->xpParaNivel($gamificacion->nivel);
    $gamificacion->nivel++;
    $subioNivel = true;
}
```

### 5.3 Racha diaria

```php
$hoy = now()->toDateString();

if ($gamificacion->ultimo_dia_activo === null) {
    $gamificacion->racha = 1;
} elseif ($gamificacion->ultimo_dia_activo->format('Y-m-d') === $hoy) {
    // ya activo hoy, no cambia
} elseif ($gamificacion->ultimo_dia_activo->diffInDays($hoy) <= 1) {
    $gamificacion->racha++;
} else {
    $gamificacion->racha = 1;
}

$gamificacion->ultimo_dia_activo = $hoy;
$gamificacion->mejor_racha = max($gamificacion->mejor_racha, $gamificacion->racha);
```

### 5.4 Histórico diario

```php
$diario = $gamificacion->registrosDiarios()->firstOrCreate(['fecha' => $hoy]);
$diario->increment('tareas');
$diario->increment('xp', $xp);
```

> Esto permite calcular con una sola query:
> - **Hoy:** `where('fecha', $hoy)`
> - **Semana:** `whereBetween('fecha', [now()->subDays(6), $hoy])`
> - **Mes:** `whereBetween('fecha', [now()->startOfMonth(), $hoy])`

### 5.5 Hitos de racha

```php
private const HITOS_RACHA = [7, 15, 30, 60, 100, 365];
```

Al registrar la racha, si `in_array($racha, HITOS_RACHA)`, dispara evento y logro.

---

## 6. Lógica de logros

Evaluación del lado del servidor tras cada tarea completada. `meta` = valor objetivo.

| Slug                  | Condición                     | Meta | Rareza      |
| --------------------- | ----------------------------- | ---- | ----------- |
| `primera-victoria`    | `tareas_completadas >= 1`     | 1    | comun       |
| `productivo`          | `tareas_completadas >= 25`    | 25   | raro        |
| `incansable`          | `tareas_completadas >= 100`   | 100  | epico       |
| `maestro-organizacion`| `tareas_completadas >= 500`   | 500  | legendario  |
| `racha-semana`        | `racha >= 7`                  | 7    | comun       |
| `racha-quince`        | `racha >= 15`                 | 15   | raro        |
| `imparable`           | `racha >= 30`                 | 30   | epico       |
| `leyenda`             | `racha >= 100`                | 100  | legendario  |
| `rapido`              | `tareas del día >= 10`        | 10   | epico       |
| `perfeccionista`      | `tareas de la semana >= 20`   | 20   | epico       |
| `madrugador`          | tarea completada antes 06:00  | —    | raro        |
| `nocturno`            | tarea completada después 23:00| —    | raro        |
| `nivel-10`            | `nivel >= 10`                 | 10   | raro        |
| `nivel-20`            | `nivel >= 20`                 | 20   | epico       |
| `nivel-35`            | `nivel >= 35`                 | 35   | epico       |
| `nivel-50`            | `nivel >= 50`                 | 50   | legendario  |

```php
private function evaluarLogros(Gamificacion $g, bool $esMadrugada, bool $esNoche): array
{
    $hoy = now()->toDateString();
    $dias = $g->registrosDiarios()->whereBetween('fecha', [now()->subDays(6), $hoy])->sum('tareas');
    $hoyTareas = $g->registrosDiarios()->where('fecha', $hoy)->value('tareas') ?? 0;

    $condiciones = [
        'primera-victoria'     => $g->tareas_completadas >= 1,
        'productivo'           => $g->tareas_completadas >= 25,
        'incansable'           => $g->tareas_completadas >= 100,
        'maestro-organizacion' => $g->tareas_completadas >= 500,
        'racha-semana'         => $g->racha >= 7,
        'racha-quince'         => $g->racha >= 15,
        'imparable'            => $g->racha >= 30,
        'leyenda'              => $g->racha >= 100,
        'rapido'               => $hoyTareas >= 10,
        'perfeccionista'       => $dias >= 20,
        'madrugador'           => $esMadrugada,
        'nocturno'             => $esNoche,
        'nivel-10'             => $g->nivel >= 10,
        'nivel-20'             => $g->nivel >= 20,
        'nivel-35'             => $g->nivel >= 35,
        'nivel-50'             => $g->nivel >= 50,
    ];

    $nuevos = [];
    foreach ($condiciones as $slug => $cumple) {
        if (!$cumple) continue;
        $logro = Logro::where('slug', $slug)->first();
        if (!$logro) continue;
        if ($g->logros()->where('logro_id', $logro->id)->exists()) continue;

        $g->logros()->attach($logro->id, ['desbloqueado_en' => now()]);
        $nuevos[] = $logro;
    }
    return $nuevos;
}
```

> `madrugador` / `nocturno` se evalúan con la hora actual del servidor
> (`now()->hour < 6` / `>= 23`) y solo aplican a eventos de tarea.

---

## 7. Seeders

`database/seeders/LogrosSeeder.php` — inserta el catálogo de §6. Debe ser idempotente
(`updateOrCreate` por `slug`).

```php
$logros = [
    ['slug' => 'primera-victoria', 'titulo' => 'Primera Victoria', 'descripcion' => 'Completa tu primera tarea.', 'icono' => 'i-lucide-trophy', 'rareza' => 'comun', 'categoria' => 'productividad', 'condicion' => 'Completa cualquier tarea', 'meta' => 1],
    // ... resto del catálogo
];
foreach ($logros as $l) {
    Logro::updateOrCreate(['slug' => $l['slug']], $l);
}
```

Registrar en `DatabaseSeeder` con `call(LogrosSeeder::class)`.

---

## 8. Integración con notificaciones

Al desbloquear un logro o alcanzar un hito, crear una **notificación** reutilizando la
tabla de notificaciones existente (tipo `logro` / `hito_racha`):

```php
use App\Models\Notificacion; // según la estructura existente

if (count($nuevosLogros) > 0) {
    foreach ($nuevosLogros as $logro) {
        Notificacion::create([
            'usuario_id' => $g->usuario_id,
            'titulo'     => 'Logro desbloqueado: ' . $logro->titulo,
            'mensaje'    => $logro->descripcion,
            'tipo'       => 'logro',
            'url'        => '/progress',
            'leida'      => false,
        ]);
    }
}
```

> Revisa la tabla `notificaciones` real del proyecto para ajustar nombres de columnas.

---

## 9. Contrato de respuesta

El frontend espera exactamente estos campos. **No cambiar nombres sin actualizar
`useGamification.ts`.**

| Campo JSON                  | Tipo                    | Uso en frontend                        |
| --------------------------- | ----------------------- | -------------------------------------- |
| `xp`                        | int                     | barra de progreso                      |
| `nivel`                     | int                     | nivel actual                           |
| `racha`                     | int                     | contador 🔥                            |
| `mejor_racha`               | int                     | estadísticas                           |
| `ultimo_dia_activo`         | string \| null (fecha)  | cálculo de racha                       |
| `tareas_completadas`        | int                     | total de tareas                        |
| `tareas_completadas_ids`    | int[]                   | anti-doble recompensa del cliente      |
| `sonidos_activados`         | bool                    | toggle de sonido                       |
| `volumen`                   | float                   | slider de volumen                      |
| `diario`                    | [{fecha,tareas,xp}]     | hoy/semana/mes + XP semanal            |
| `logros`                    | [{slug,desbloqueado_en}]| estado de logros desbloqueados         |
| `xp_ganado`                 | int                     | popup "+XP"                            |
| `subio_nivel`               | bool                    | modal de nivel                         |
| `nuevo_nivel`               | int \| null             | nivel alcanzado                        |
| `hito_racha`                | int \| null             | celebración de racha                   |
| `logros_nuevos`             | [{slug,titulo,icono,rareza}] | modales de logro               |
| `perfil`                    | objeto de arriba        | estado completo refrescado             |
