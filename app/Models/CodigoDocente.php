<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoDocente extends Model
{
    use HasFactory;

    protected $table = 'codigos_docentes';

    protected $fillable = [
        'codigo',
        'descripcion_nota',
        'usado',
        'usado_por_id',
        'usado_en',
        'creado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'usado'    => 'boolean',
            'usado_en' => 'datetime',
        ];
    }

    /**
     * Profesor que canjeó este código al registrarse.
     */
    public function usadoPor()
    {
        return $this->belongsTo(User::class, 'usado_por_id');
    }

    /**
     * Administrador que generó este código.
     */
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}

