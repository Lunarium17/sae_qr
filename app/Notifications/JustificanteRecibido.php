<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JustificanteRecibido extends Notification
{
    use Queueable;

    public function __construct(
        public string $alumnoNombre,
        public string $materiaNombre,
        public string $incidencia,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tipo'   => 'justificante',
            'titulo' => 'Justificante recibido',
            'texto'  => $this->alumnoNombre . ' subió un comprobante por ' . $this->incidencia . ' en ' . $this->materiaNombre . '.',
            'icono'  => 'bi-upload',
            'url'    => route('profesor.justificantes'),
        ];
    }
}
