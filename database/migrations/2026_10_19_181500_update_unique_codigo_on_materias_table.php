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
        $oldIdx = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_codigo_materia_unique'");
        if (!empty($oldIdx)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->dropUnique('materias_codigo_materia_unique');
            });
        }

        $newIdx = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_grupo_codigo_unique'");
        if (empty($newIdx)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->unique(['grupo_id', 'codigo_materia'], 'materias_grupo_codigo_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $newIdx = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_grupo_codigo_unique'");
        if (!empty($newIdx)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->dropUnique('materias_grupo_codigo_unique');
            });
        }

        $oldIdx = DB::select("SHOW INDEX FROM materias WHERE Key_name = 'materias_codigo_materia_unique'");
        if (empty($oldIdx)) {
            Schema::table('materias', function (Blueprint $table) {
                $table->unique('codigo_materia', 'materias_codigo_materia_unique');
            });
        }
    }
};
