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
        // * Los semestres vienen del seeder; la aplicación no los crea ni los edita.
        Schema::create('semestres', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero')->unique();
            $table->string('nombre', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semestres');
    }
};
