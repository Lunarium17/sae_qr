<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grupos') && !Schema::hasColumn('grupos', 'carrera')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->string('carrera', 120)->nullable()->after('nombre_grupo');
            });
        }

        if (Schema::hasTable('justificantes') && !Schema::hasColumn('justificantes', 'comentario_profesor')) {
            Schema::table('justificantes', function (Blueprint $table) {
                $table->string('comentario_profesor', 500)->nullable()->after('motivo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grupos') && Schema::hasColumn('grupos', 'carrera')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropColumn('carrera');
            });
        }

        if (Schema::hasTable('justificantes') && Schema::hasColumn('justificantes', 'comentario_profesor')) {
            Schema::table('justificantes', function (Blueprint $table) {
                $table->dropColumn('comentario_profesor');
            });
        }
    }
};

