<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    protected $fillable = [
        'nombre_materia',
        'codigo_materia',
    ];

    // Relación con matrículas
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'materia_id');
    }
}