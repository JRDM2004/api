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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cuenta', 20)->unique();
            $table->string('nombre', 100);
            $table->string('correo', 150)->nullable();
            $table->string('nip_hash');
            $table->enum('rol', ['alumno', 'docente', 'administrador']);
            $table->string('telefono', 20)->nullable();
            $table->string('foto_ruta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();

            $table->index(['rol', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
