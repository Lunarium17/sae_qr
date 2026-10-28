<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            // Eliminar el índice único global de codigo_materia y reemplazarlo por único compuesto (grupo_id + codigo_materia)
            $table->dropUnique('materias_codigo_materia_unique');
            $table->unique(['grupo_id', 'codigo_materia'], 'materias_grupo_codigo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique('materias_grupo_codigo_unique');
            $table->unique('codigo_materia', 'materias_codigo_materia_unique');
        });
    }
};
