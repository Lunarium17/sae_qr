<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudInscripcionNueva extends Notification
{
    use Queueable;

    public function __construct(
        public string $alumnoNombre,
        public string $materiaNombre,
        public string $grupoNombre,
        public int $materiaId,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tipo'   => 'solicitud',
            'titulo' => 'Nueva solicitud de inscripción',
            'texto'  => $this->alumnoNombre . ' solicita inscribirse en ' . $this->materiaNombre . ' · ' . $this->grupoNombre . '.',
            'icono'  => 'bi-person-plus-fill',
            'url'    => route('profesor.solicitudes', ['materia_id' => $this->materiaId]),
        ];
    }
}
