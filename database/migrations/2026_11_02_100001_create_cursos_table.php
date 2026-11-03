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
        // * Un curso es una materia impartida a un grupo. Su nombre y su clave vienen de la materia.
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias');
            // index() porque se buscan los cursos de cada grupo.
            $table->foreignId('grupo_id')->index()->constrained('grupos');
            // El docente es opcional al crear. index() porque se buscan los cursos de cada docente.
            $table->foreignId('docente_id')->nullable()->index()->constrained('usuarios');
            $table->timestampsTz();

            // Una materia solo puede tener un curso por grupo.
            $table->unique(['materia_id', 'grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
