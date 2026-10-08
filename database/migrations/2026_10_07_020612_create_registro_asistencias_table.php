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
        Schema::create('registro_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_clase_id')->constrained('sesion_clases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Refiere al alumno
            $table->timestamp('timestamp_escaneo')->nullable(); // Marca de tiempo del escaneo
            $table->enum('estado_incidencia', ['asistencia', 'retardo', 'falta_justificada', 'falta_injustificada', 'sin_registro'])->default('sin_registro'); // Estado de asistencia
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_asistencias');
    }
};
