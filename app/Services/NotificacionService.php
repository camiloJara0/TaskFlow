<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    public static function notificar(
        int $destinatarioId,
        string $titulo,
        string $mensaje,
        string $tipo,
        ?string $url = null,
        ?Mailable $mailable = null
    ): ?Notificacion
    {
        if ($destinatarioId === Auth::id()) {
            return null;
        }

        $notificacion = Notificacion::create([
            'usuario_id' => $destinatarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'url' => $url,
        ]);

        $destinatario = User::find($destinatarioId);

        if ($mailable && $destinatario) {
            try {
                Mail::to($destinatario->email)->send($mailable);
            } catch (\Exception $e) {
                \Log::error('Error al enviar correo de notificación', ['exception' => $e]);
            }
        }

        self::enviarPush($destinatario, $titulo, $mensaje, $url);

        return $notificacion;
    }

    private static function enviarPush(?User $destinatario, string $titulo, string $mensaje, ?string $url): void
    {
        if (!$destinatario) {
            return;
        }

        try {
            app(\App\Services\PushNotificationService::class)->sendToUser(
                $destinatario,
                \App\Services\PushNotificationService::payload($titulo, $mensaje, $url)
            );
        } catch (\Throwable $e) {
            \Log::error('Error al enviar push de notificación', ['exception' => $e]);
        }
    }

    public static function urlFrontend(string $ruta): string
    {
        $base = config('app.frontend_url', 'http://localhost:3000');
        return rtrim($base, '/') . '/' . ltrim($ruta, '/');
    }
}
