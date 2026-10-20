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
        'nombre_materia',
        'codigo_materia',
    ];

    // Relación con el grupo al que pertenece la materia
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Relación con matrículas
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'materia_id');
    }
}