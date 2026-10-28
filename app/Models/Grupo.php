<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre_grupo',
        'carrera',
        'grado',
        'turno',
        'periodo_lectivo',
        'profesor_id',
    ];

    // Relación con las materias que se imparten en este grupo (por distintos docentes)
    public function materias()
    {
        return $this->hasMany(Materia::class, 'grupo_id');
    }

    // Relación con matrículas individuales vinculadas a este grupo
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'grupo_id');
    }

    // Relación con sesiones de clase impartidas en este grupo
    public function sesiones()
    {
        return $this->hasMany(SesionClase::class, 'grupo_id');
    }
}
