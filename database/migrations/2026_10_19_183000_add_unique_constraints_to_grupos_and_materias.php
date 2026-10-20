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
        Schema::table('grupos', function (Blueprint $table) {
            $table->unique(['nombre_grupo', 'turno'], 'grupos_nombre_turno_unique');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->unique(['grupo_id', 'nombre_materia'], 'materias_grupo_nombre_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropUnique('grupos_nombre_turno_unique');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->dropUnique('materias_grupo_nombre_unique');
        });
    }
};

