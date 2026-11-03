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
        // * Qué alumnos integran cada grupo. La baja no borra la fila: solo cambia el estado.
        Schema::create('grupo_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos');
            $table->foreignId('alumno_id')->constrained('usuarios');
            $table->enum('estado', ['activo', 'baja'])->default('activo');
            $table->timestampsTz();
            $table->timestampTz('baja_en')->nullable();

            // Un alumno aparece una sola vez en cada grupo; si reingresa, se reusa su fila.
            $table->unique(['grupo_id', 'alumno_id']);
            // Para encontrar rápido en qué grupo está activo un alumno.
            $table->index(['alumno_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_alumnos');
    }
};
