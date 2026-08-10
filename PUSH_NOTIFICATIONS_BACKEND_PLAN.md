# Plan Backend: Notificaciones Push

## Objetivo

Permitir que el frontend (PWA TaskFlow) registre un token de suscripción Web Push y que el backend pueda enviar notificaciones al navegador del usuario, incluso cuando la app no está abierta.

## Requisitos previos

- Laravel 11+ (mismo stack que el backend actual).
- Paquete `minishlink/web-push`:

```bash
composer require minishlink/web-push
```

- Generar claves VAPID (una sola vez):

```bash
php artisan webpush:generate  # o con minishlink/web-push
```

Las claves (`VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`) van al `.env` y se publican como configuración.

## Tabla

### `push_tokens`

| Columna        | Tipo                          | Descripción                                  |
| -------------- | ----------------------------- | -------------------------------------------- |
| `id`           | bigint (PK, autoincrement)    |                                              |
| `user_id`      | foreignId → `users.id`        | Usuario dueño del token                      |
| `token`        | text                          | Endpoint de suscripción (`endpoint`)         |
| `navegador`    | string, nullable              | `userAgent` del navegador                    |
| `created_at`   | timestamp                     |                                              |
| `updated_at`   | timestamp                     |                                              |

- Índice único: `user_id + token` (evita duplicados por usuario/browser).
- Índice sobre `token` para el borrado por endpoint.

Migración:

```php
Schema::create('push_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('token');
    $table->string('navegador')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'token']);
});
```

Modelo `PushToken`:

```php
class PushToken extends Model
{
    protected $fillable = ['user_id', 'token', 'navegador'];
}
```

## Endpoints (todos con `auth:sanctum`)

### `POST /api/v1/push-tokens`

Registra (o re-registra) la suscripción del usuario autenticado.

- **Request:**
  ```json
  { "token": "https://fcm.googleapis.com/...", "navegador": "Mozilla/5.0 ..." }
  ```
- **Reglas de validación:** `token` requerido (string), `navegador` nullable (string).
- **Lógica:** `updateOrCreate(['user_id' => $user->id, 'token' => $token], ['navegador' => $navegador])`.
- **Respuesta:** `200` `{ success: true, message: 'Token registrado' }`.

### `DELETE /api/v1/push-tokens/{token}`

Elimina la suscripción del usuario autenticado.

- **Lógica:** borrar solo si `user_id === auth()->id()` y `token` coincide.
- **Respuesta:** `200` `{ success: true }` (o `404` si no existe).

## Envío de notificaciones

Servicio `App\Services\PushNotificationService`:

```php
use Minishlink\WebPush\WebPush;

public function __construct(private readonly WebPush $webPush) {}

public function send(PushToken $token, array $payload): void
{
    $this->webPush->queueNotification(
        $token->token,
        json_encode($payload)
    );
    $this->webPush->flush();
}

public function sendToUser(User $user, array $payload): void
{
    foreach ($user->pushTokens as $token) {
        $this->send($token, $payload);
    }
}
```

Config VAPID (service provider o `config/webpush.php`):

```php
$webPush = new WebPush([
    'VAPID' => [
        'subject' => env('VAPID_SUBJECT'),
        'publicKey' => env('VAPID_PUBLIC_KEY'),
        'privateKey' => env('VAPID_PRIVATE_KEY'),
    ],
]);
```

### Payload de la notificación

```json
{
  "title": "Tarea completada",
  "body": "Completaste «Revisar PR» y ganaste 50 XP",
  "url": "/tasks/42",
  "icon": "/pwa-192x192.png",
  "badge": "/pwa-192x192.png",
  "actions": [{ "action": "open", "title": "Ver tarea" }]
}
```

El service worker del frontend (`app/sw.ts`) ya interpreta este formato: muestra la notificación con `title`/`body`, y al hacer click abre `url`.

## Dónde disparar el envío

- Al completar una tarea (integración con la lógica de gamificación): "¡Has completado una tarea!".
- Al asignar una tarea a un usuario.
- Al crear un comentario/mención.
- Al vencer una tarea o un recordatorio.

Ejemplo de disparador en un evento:

```php
event(new TaskCompleted($task)); // listener → PushNotificationService::sendToUser($task->user, $payload)
```

## Flujo completo (frontend ↔ backend)

1. Usuario activa "Notificaciones push" en `settings.vue`.
2. `usePushNotifications.toggle(true)`:
   - Pide permiso con `Notification.requestPermission()`.
   - `navigator.serviceWorker.ready` → `pushManager.subscribe({ userVisibleOnly, applicationServerKey })`.
   - `POST /api/v1/push-tokens` con `endpoint` + `userAgent`.
3. El backend guarda el token en `push_tokens`.
4. Cuando ocurre un evento, el backend envía el payload Web Push a cada token del usuario.
5. El navegador despierta el service worker (`push` event) → `showNotification`.
6. Si el usuario hace clic, `notificationclick` abre la URL indicada.
7. Al desactivar, `unsubscribe()` + `DELETE /api/v1/push-tokens/{endpoint}`.

## Seguridad

- `token` es un endpoint de suscripción (secreto por diseño); solo su dueño puede registrarlo/borrarlo.
- Limpiar tokens muertos: si `flush()` devuelve un error con código `404`/`410` (suscripción eliminada), borrar el `PushToken`.
- No enviar a tokens de usuarios desactivados.

## Verificación

- `POST /api/v1/push-tokens` con token real de Chrome devuelve 200.
- Enviar un payload de prueba y ver la notificación con la app cerrada.
- `DELETE /api/v1/push-tokens/{token}` elimina y deja de recibir push.
