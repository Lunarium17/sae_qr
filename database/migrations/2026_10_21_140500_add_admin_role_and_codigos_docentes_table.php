<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliar el ENUM de la columna 'rol' en la tabla 'users' para incluir 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('admin', 'profesor', 'alumno') NOT NULL");

        // 2. Crear tabla 'codigos_docentes' para códigos de invitación de un solo uso
        if (!Schema::hasTable('codigos_docentes')) {
            Schema::create('codigos_docentes', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 30)->unique();
                $table->string('descripcion_nota', 150)->nullable();
                $table->boolean('usado')->default(false);
                $table->foreignId('usado_por_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('usado_en')->nullable();
                $table->foreignId('creado_por_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Crear cuenta inicial de Administrador Escolar si no existe
        $existeAdmin = DB::table('users')->whereIn('email', ['admin@saeqr.com', 'admin@sae_qr.com'])->exists();
        if (!$existeAdmin) {
            DB::table('users')->insert([
                'name'             => 'Administrador Escolar',
                'email'            => 'admin@saeqr.com',
                'password'         => Hash::make('admin123'),
                'rol'              => 'admin',
                'matricula_codigo' => 'ADM-001',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'admin@sae_qr.com')->update(['email' => 'admin@saeqr.com']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_docentes');
        DB::table('users')->where('rol', 'admin')->delete();
        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('profesor', 'alumno') NOT NULL");
    }
};

