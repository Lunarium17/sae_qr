<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
    use HasFactory;

    protected $table = 'registro_asistencias';

    protected $fillable = [
        'sesion_clase_id',
        'user_id',
        'timestamp_escaneo',
        'estado_incidencia', // 'asistencia', 'retardo', 'falta', 'permiso', 'sin_registro'
        'observaciones',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionClase::class, 'sesion_clase_id');
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}