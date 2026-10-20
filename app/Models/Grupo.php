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
        'grado',
        'turno',
        'periodo_lectivo',
        'profesor_id', // Si tu migración vincula el grupo al profesor creador
    ];

    // Relación con las materias que pertenecen a este grupo
    public function materias()
    {
        return $this->hasMany(Materia::class, 'grupo_id');
    }

    // Relación con matrículas
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'grupo_id');
    }
}