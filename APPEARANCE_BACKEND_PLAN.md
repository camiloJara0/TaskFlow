# Plan Backend: Preferencias de Apariencia

## Objetivo

Persistir las preferencias de apariencia del usuario (tamaño de fuente, animaciones reducidas, imagen de fondo y tema sólido) en el backend para que se sincronicen entre dispositivos y navegadores.

El frontend ya aplica estas preferencias localmente (`useAppearance` → `localStorage`). Este plan describe cómo el backend debe guardarlas para que la configuración sobreviva al cambio de dispositivo.

## Preferencias

| Campo                | Clave               | Tipo     | Valores                      | Default    |
| -------------------- | ------------------- | -------- | ---------------------------- | ---------- |
| Tamaño de fuente     | `font_size`         | string   | `small` \| `medium` \| `large` | `medium`   |
| Animaciones reducidas | `animaciones_reducidas` | boolean | `true` \| `false`            | `false`    |
| Imagen de fondo      | `imagen_fondo`      | string   | URL (vacío = aurora)         | `''`       |
| Tema sólido          | `tema_solido`       | boolean  | `true` \| `false`            | `false`    |

## Migración

Columna JSON en `users` (reutiliza el modelo `User` que ya tiene `tema`):

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('apariencia')->nullable()->after('tema');
});
```

Alternativa (si se prefiere no tocar `users`): tabla `preferencias_apariencia` con `user_id` FK único.

Modelo `User`:

```php
protected $casts = [
    'apariencia' => 'array',
];
```

## Endpoints (con `auth:sanctum`)

### `GET /api/v1/perfil/apariencia`

Devuelve las preferencias del usuario autenticado.

- **Lógica:** `auth()->user()->apariencia ?? defaults`.
- **Respuesta:** `200`
  ```json
  {
    "success": true,
    "data": {
      "font_size": "medium",
      "animaciones_reducidas": false,
      "imagen_fondo": "",
      "tema_solido": false
    }
  }
  ```

### `PUT /api/v1/perfil/apariencia`

Guarda (merge) las preferencias del usuario autenticado.

- **Request:**
  ```json
  { "font_size": "large", "animaciones_reducidas": true, "imagen_fondo": "", "tema_solido": true }
  ```
- **Reglas de validación:**
  - `font_size`: `nullable|in:small,medium,large`
  - `animaciones_reducidas`: `nullable|boolean`
  - `imagen_fondo`: `nullable|string|max:1000` (URL; vacío = ninguna)
  - `tema_solido`: `nullable|boolean`
- **Lógica:**
  ```php
  $user = auth()->user();
  $user->apariencia = array_merge(
      $user->apariencia ?? $defaults,
      $request->validated()
  );
  $user->save();
  ```
- **Respuesta:** `200` `{ success: true, data: <apariencia actualizada> }`.

## Sincronización con el frontend

El frontend (`app/composables/useAppearance.ts`) ya guarda la configuración en `localStorage` para respuesta inmediata. La sincronización con el backend es best-effort:

1. Al cargar la app (usuario autenticado): `GET /perfil/apariencia` → aplicar a `useAppearance`.
2. Al cambiar cualquier opción en `settings.vue`: `PUT /perfil/apariencia` (con `catch` silencioso, igual que `useGamification.setSoundEnabled`).
3. El `localStorage` local mantiene el estado mientras no haya conexión.

Contrato del servicio frontend sugerido (`app/composables/services/profile.ts`):

```ts
async function getAppearance() {
  return request<ApiResponse<AppearanceSettings>>('/perfil/apariencia')
}

async function updateAppearance(data: Partial<AppearanceSettings>) {
  return request<ApiResponse<AppearanceSettings>>('/perfil/apariencia', {
    method: 'PUT',
    body: data
  })
}
```

## Seguridad

- Ambos endpoints exigen `auth:sanctum`.
- El valor de `imagen_fondo` es una URL; el backend solo la almacena (nunca la fetchea).
- No exponer la configuración de otros usuarios.

## Verificación

- `GET /perfil/apariencia` devuelve defaults la primera vez.
- `PUT /perfil/apariencia` con `font_size=large` y luego `GET` devuelve el valor actualizado.
- El cambio persiste entre sesiones y dispositivos.
