<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grupos') && !Schema::hasColumn('grupos', 'profesor_id')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->foreignId('profesor_id')
                    ->nullable()
                    ->after('periodo_lectivo')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grupos') && Schema::hasColumn('grupos', 'profesor_id')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign(['profesor_id']);
                $table->dropColumn('profesor_id');
            });
        }
    }
};
