<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionClase extends Model
{
    use HasFactory;

    protected $table = 'sesion_clases';

    protected $fillable = [
        'profesor_id',
        'grupo_id',
        'materia_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tolerancia_minutos',
        'estado', // 'activa', 'finalizada'
    ];

    // Docente que imparte la sesión
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // Grupo específico de la sesión (Gestión Modular del Docente)
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Asignatura específica de la sesión
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    // Registros de asistencia de esta sesión
    public function asistencias()
    {
        return $this->hasMany(RegistroAsistencia::class, 'sesion_clase_id');
    }
}
