<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JustificanteDecision extends Notification
{
    use Queueable;

    public function __construct(
        public string $estado,
        public string $materiaNombre,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        if ($this->estado === 'aceptado') {
            return [
                'tipo'   => 'justificante',
                'titulo' => 'Justificante aceptado',
                'texto'  => 'Tu justificante de ' . $this->materiaNombre . ' fue aceptado por el docente.',
                'icono'  => 'bi-check-circle-fill',
                'url'    => route('alumno.justificantes'),
            ];
        }

        return [
            'tipo'   => 'justificante',
            'titulo' => 'Justificante rechazado',
            'texto'  => 'Tu justificante de ' . $this->materiaNombre . ' fue rechazado. Revisa el comentario del docente.',
            'icono'  => 'bi-x-circle-fill',
            'url'    => route('alumno.justificantes'),
        ];
    }
}
