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
        Schema::table('sesion_clases', function (Blueprint $table) {
            $table->boolean('archivado')->default(false)->after('estado')->index();
            $table->string('periodo_lectivo')->nullable()->after('archivado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesion_clases', function (Blueprint $table) {
            $table->dropIndex(['archivado']);
            $table->dropColumn(['archivado', 'periodo_lectivo']);
        });
    }
};
