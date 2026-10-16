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
        // * Registro de acciones sensibles: quién hizo qué, a quién y cuándo.
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('usuarios');
            $table->string('actor_nombre', 100);
            $table->string('actor_numero_cuenta', 20);
            $table->string('accion', 50);
            $table->string('objeto_tipo', 30);
            $table->unsignedBigInteger('objeto_id');
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
