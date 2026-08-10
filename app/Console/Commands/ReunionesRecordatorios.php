<?php

namespace App\Console\Commands;

use App\Models\Reunion;
use App\Services\NotificacionService;
use Illuminate\Console\Command;

class ReunionesRecordatorios extends Command
{
    protected $signature = 'reuniones:recordatorios';

    protected $description = 'Envía recordatorios de reuniones que comienzan pronto';

    public function handle(): int
    {
        $procesadas = 0;

        $reuniones = Reunion::with(['integrantes.usuario'])
            ->where('archivada', false)
            ->where('recordatorio_enviado', false)
            ->whereNotIn('estado', ['finalizada', 'cancelada'])
            ->whereRaw("CONCAT(fecha, ' ', hora) BETWEEN ? AND ?", [
                now()->format('Y-m-d H:i:00'),
                now()->addMinutes(15)->format('Y-m-d H:i:59'),
            ])
            ->get();

        foreach ($reuniones as $reunion) {
            $url = NotificacionService::urlFrontend("reuniones/{$reunion->id}");
            $titulo = 'Reunión próxima';
            $mensaje = "La reunión '{$reunion->titulo}' comienza hoy a las {$reunion->hora}.";

            foreach ($reunion->integrantes as $integrante) {
                if (!$integrante->usuario) {
                    continue;
                }

                NotificacionService::notificar(
                    $integrante->id_usuario,
                    $titulo,
                    $mensaje,
                    'recordatorio',
                    $url
                );
            }

            $reunion->update(['recordatorio_enviado' => true]);
            $procesadas++;
        }

        $this->info("Recordatorios de reuniones procesados: {$procesadas}");

        return self::SUCCESS;
    }
}
