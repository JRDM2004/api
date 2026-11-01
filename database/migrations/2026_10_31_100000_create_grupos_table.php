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
        // * Un grupo es un salón que cursa un semestre. Ejemplo: grupo "3-2", semestre 3.
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            // index() porque la lista de grupos se filtra por semestre.
            $table->foreignId('semestre_id')->index()->constrained('semestres');
            $table->string('nombre', 20)->unique();
            // El turno es opcional.
            $table->enum('turno', ['matutino', 'vespertino'])->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
