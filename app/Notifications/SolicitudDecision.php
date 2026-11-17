<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudDecision extends Notification
{
    use Queueable;

    public function __construct(
        public string $estado,
        public string $materiaNombre,
        public string $grupoNombre,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        if ($this->estado === 'aceptado') {
            return [
                'tipo'   => 'solicitud',
                'titulo' => 'Solicitud aceptada',
                'texto'  => 'Tu inscripción en ' . $this->materiaNombre . ' · ' . $this->grupoNombre . ' fue aceptada.',
                'icono'  => 'bi-check-circle-fill',
                'url'    => route('alumno.materias'),
            ];
        }

        return [
            'tipo'   => 'solicitud',
            'titulo' => 'Solicitud rechazada',
            'texto'  => 'Tu solicitud de ' . $this->materiaNombre . ' · ' . $this->grupoNombre . ' fue rechazada.',
            'icono'  => 'bi-x-circle-fill',
            'url'    => route('alumno.materias'),
        ];
    }
}
