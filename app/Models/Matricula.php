<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;

    protected $table = 'matriculas';

    protected $fillable = [
        'user_id',
        'grupo_id',
        'materia_id',
        'estado', // 'pendiente', 'aceptado', 'rechazado'
    ];

    // Relación con el Alumno
    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Relación con la Materia
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}