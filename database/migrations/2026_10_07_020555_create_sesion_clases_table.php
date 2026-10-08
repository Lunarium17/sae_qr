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
        Schema::create('sesion_clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('tolerancia_minutos')->default(15);
            $table->enum('estado', ['activa', 'finalizada'])->default('activa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesion_clases');
    }
};
