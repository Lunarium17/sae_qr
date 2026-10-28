<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    protected $fillable = [
        'grupo_id',
        'profesor_id',
        'nombre_materia',
        'codigo_materia',
    ];

    // Relación con el grupo académico en el que se imparte la asignatura
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Relación con el docente titular que imparte esta asignatura en ese grupo
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // Relación con matrículas individuales (Alumno + Grupo + Materia)
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'materia_id');
    }

    // Relación con las sesiones de clase de esta asignatura
    public function sesiones()
    {
        return $this->hasMany(SesionClase::class, 'materia_id');
    }
}
