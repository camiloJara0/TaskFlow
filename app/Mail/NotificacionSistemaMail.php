<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionSistemaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $destinatario;
    public $datos;

    public function __construct(User $destinatario, array $datos)
    {
        $this->destinatario = $destinatario;
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->subject($this->datos['asunto'] ?? 'Notificación del sistema')
                    ->view('emails.notificacion_sistema');
    }
}
