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
        // * Las materias del plan de estudios. No se borran: se desactivan.
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            // index() porque la lista de materias siempre se agrupa por semestre.
            $table->foreignId('semestre_id')->index()->constrained('semestres');
            $table->string('clave', 20)->unique();
            $table->string('nombre', 150);
            $table->boolean('activa')->default(true);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
