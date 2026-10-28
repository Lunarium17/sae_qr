<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adapta el esquema al ámbito universitario:
     * 1. Cada Materia dentro de un Grupo se vincula al Profesor que la imparte (profesor_id).
     * 2. SesionClase vincula explícitamente profesor_id + grupo_id + materia_id al mismo tiempo.
     * 3. Matricula garantiza unicidad por combinación (user_id, grupo_id, materia_id).
     */
    public function up(): void
    {
        // 1. Agregar profesor_id a la tabla 'materias'
        if (!Schema::hasColumn('materias', 'profesor_id')) {
            Schema::table('materias', function (Blueprint $table) {
                $table->foreignId('profesor_id')
                    ->nullable()
                    ->after('grupo_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });

            // Si ya existen materias sin profesor_id, asignarlas al primer profesor registrado
            $primerProfesorId = DB::table('users')->where('rol', 'profesor')->value('id');
            if ($primerProfesorId) {
                DB::table('materias')->whereNull('profesor_id')->update(['profesor_id' => $primerProfesorId]);
            }
        }

        // 2. Agregar grupo_id a la tabla 'sesion_clases' para vincular (profesor_id, grupo_id, materia_id)
        if (!Schema::hasColumn('sesion_clases', 'grupo_id')) {
            Schema::table('sesion_clases', function (Blueprint $table) {
                $table->foreignId('grupo_id')
                    ->nullable()
                    ->after('profesor_id')
                    ->constrained('grupos')
                    ->cascadeOnDelete();
            });

            // Rellenar grupo_id en sesiones existentes a partir de su materia
            $sesiones = DB::table('sesion_clases')->whereNull('grupo_id')->get();
            foreach ($sesiones as $sesion) {
                $grupoId = DB::table('materias')->where('id', $sesion->materia_id)->value('grupo_id');
                if ($grupoId) {
                    DB::table('sesion_clases')->where('id', $sesion->id)->update(['grupo_id' => $grupoId]);
                }
            }
        }

        // 3. Asegurar índice único en matriculas por (user_id, grupo_id, materia_id)
        // Primero limpiamos posibles duplicados históricos si existieran
        $duplicados = DB::table('matriculas')
            ->select('user_id', 'grupo_id', 'materia_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'grupo_id', 'materia_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicados as $dup) {
            DB::table('matriculas')
                ->where('user_id', $dup->user_id)
                ->where('grupo_id', $dup->grupo_id)
                ->where('materia_id', $dup->materia_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        $indices = DB::select("SHOW INDEX FROM matriculas WHERE Key_name = 'matriculas_alumno_grupo_materia_unique'");
        if (empty($indices)) {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->unique(['user_id', 'grupo_id', 'materia_id'], 'matriculas_alumno_grupo_materia_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indices = DB::select("SHOW INDEX FROM matriculas WHERE Key_name = 'matriculas_alumno_grupo_materia_unique'");
        if (!empty($indices)) {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->dropUnique('matriculas_alumno_grupo_materia_unique');
            });
        }

        if (Schema::hasColumn('sesion_clases', 'grupo_id')) {
            Schema::table('sesion_clases', function (Blueprint $table) {
                $table->dropForeign(['grupo_id']);
                $table->dropColumn('grupo_id');
            });
        }

        if (Schema::hasColumn('materias', 'profesor_id')) {
            Schema::table('materias', function (Blueprint $table) {
                $table->dropForeign(['profesor_id']);
                $table->dropColumn('profesor_id');
            });
        }
    }
};

