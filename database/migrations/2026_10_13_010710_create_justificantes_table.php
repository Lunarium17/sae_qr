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
        Schema::create('justificantes', function (Blueprint $table) {
            $table->id();
             // relacion justificante con registro de asistencias especifico
            $table->foreignId('registro_id')->constrained('registro_asistencias')->onDelete('cascade');
            // datos del justificante
            $table->text('motivo');
            $table->string('documento_url')->nullable(); // Ruta del documento adjunto
            $table->enum('estado_aprobacion', ['pendiente', 'aceptado', 'rechazado'])->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificantes');
    }
};
