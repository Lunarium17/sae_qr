<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificante extends Model
{
    use HasFactory;

    protected $table = 'justificantes';

    protected $fillable = [
        'registro_id',
        'motivo',
        'documento_url',
        'estado_aprobacion', // 'pendiente', 'aceptado', 'rechazado'
    ];

    // Relación con el registro de asistencia específico
    public function registroAsistencia()
    {
        return $this->belongsTo(RegistroAsistencia::class, 'registro_id');
    }
}