<?php

namespace App\Console\Commands;

use App\Models\Notificacion;
use App\Models\Recordatorio;
use App\Models\Tarea;
use App\Services\NotificacionService;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificacionesVencidas extends Command
{
    protected $signature = 'notificaciones:vencidas';

    protected $description = 'Notifica recordatorios vencidos y tareas cuyo vencimiento llegó';

    public function handle(): int
    {
        $procesadas = 0;
        $procesadas += $this->notificarRecordatorios();
        $procesadas += $this->notificarTareasVencidas();

        $this->info("Notificaciones de vencimiento procesadas: {$procesadas}");

        return self::SUCCESS;
    }

    private function notificarRecordatorios(): int
    {
        $procesadas = 0;

        $recordatorios = Recordatorio::with(['usuario', 'tarea'])
            ->where('enviado', false)
            ->where('fecha', '<=', now())
            ->get();

        foreach ($recordatorios as $recordatorio) {
            $usuario = $recordatorio->usuario;
            if (!$usuario) {
                continue;
            }

            $url = NotificacionService::urlFrontend("tareas/{$recordatorio->tarea_id}");
            $titulo = $recordatorio->tarea
                ? "Recordatorio: {$recordatorio->tarea->titulo}"
                : 'Recordatorio de tarea';
            $mensaje = 'No olvides tu tarea pendiente.';

            Notificacion::create([
                'usuario_id' => $usuario->id,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => 'recordatorio',
                'url' => $url,
            ]);

            $recordatorio->update(['enviado' => true]);

            $this->enviarPush($usuario, $titulo, $mensaje, $url);
            $procesadas++;
        }

        return $procesadas;
    }

    private function notificarTareasVencidas(): int
    {
        $procesadas = 0;

        $tareas = Tarea::with(['responsable', 'creador'])
            ->where('vencimiento_notificado', false)
            ->where('archivada', false)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now())
            ->get();

        foreach ($tareas as $tarea) {
            $url = NotificacionService::urlFrontend("tareas/{$tarea->id}");
            $titulo = 'Tarea vencida';
            $mensaje = "La tarea '{$tarea->titulo}' venció el " . $tarea->fecha_vencimiento->format('d/m/Y H:i') . '.';

            $destinatarios = array_unique(array_filter([
                $tarea->responsable_id,
                $tarea->creador_id,
            ]));

            foreach ($destinatarios as $destinatarioId) {
                NotificacionService::notificar(
                    $destinatarioId,
                    $titulo,
                    $mensaje,
                    'sistema',
                    $url
                );
            }

            $tarea->update(['vencimiento_notificado' => true]);
            $procesadas++;
        }

        return $procesadas;
    }

    private function enviarPush($usuario, string $titulo, string $mensaje, ?string $url): void
    {
        try {
            app(PushNotificationService::class)->sendToUser(
                $usuario,
                PushNotificationService::payload($titulo, $mensaje, $url)
            );
        } catch (\Throwable $e) {
            \Log::error('Error enviando push de recordatorio', ['exception' => $e]);
        }
    }
}
