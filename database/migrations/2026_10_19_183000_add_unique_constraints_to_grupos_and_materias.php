<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $idxGrupo = DB::select("SHOW INDEX FROM grupos WHERE Key_name = 'grupos_nombre_turno_unique'");
        if (empty($idxGrupo)) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->unique(['nombre_grupo', 'turno'], 'grupos_nombre_turno_unique');
            });
        }

        $idxMateria = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_grupo_nombre_unique'");
        if (empty($idxMateria)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->unique(['grupo_id', 'nombre_materia'], 'materias_grupo_nombre_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $idxGrupo = DB::select("SHOW INDEX FROM grupos WHERE Key_name = 'grupos_nombre_turno_unique'");
        if (!empty($idxGrupo)) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropUnique('grupos_nombre_turno_unique');
            });
        }

        $idxMateria = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_grupo_nombre_unique'");
        if (!empty($idxMateria)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->dropUnique('materias_grupo_nombre_unique');
            });
        }
    }
};
