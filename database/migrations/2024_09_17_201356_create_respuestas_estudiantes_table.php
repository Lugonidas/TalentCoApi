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
        Schema::create('respuestas_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_evaluacion')->constrained('evaluaciones'); // Relación con la evaluación
            $table->foreignId('id_estudiante')->constrained('users'); // Relación con el estudiante (users o estudiantes)
            $table->string('archivo')->nullable(); // Archivo de la respuesta (si es un archivo)
            $table->text('texto_respuesta')->nullable(); // Respuesta en texto (si es respuesta escrita)
            $table->date('fecha_entrega')->nullable(); // Fecha de entrega de la respuesta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_estudiantes');
    }
};
