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
        'materia_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tolerancia_minutos',
        'estado', // 'activa', 'finalizada'
    ];

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function asistencias()
    {
        return $this->hasMany(RegistroAsistencia::class, 'sesion_clase_id');
    }
}