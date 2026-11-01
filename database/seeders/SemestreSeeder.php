<?php

namespace Database\Seeders;

use App\Models\Semestre;
use Illuminate\Database\Seeder;

class SemestreSeeder extends Seeder
{
    /**
     * Los 8 semestres del plan de estudios.
     *
     * ! No son datos de prueba: la aplicación los necesita en todos los entornos.
     */
    public function run(): void
    {
        $semestres = [
            1 => 'Semestre I',
            2 => 'Semestre II',
            3 => 'Semestre III',
            4 => 'Semestre IV',
            5 => 'Semestre V',
            6 => 'Semestre VI',
            7 => 'Semestre VII',
            8 => 'Semestre VIII',
        ];

        // Busca por número para poder correrlo varias veces sin duplicar.
        foreach ($semestres as $numero => $nombre) {
            Semestre::updateOrCreate(
                ['numero' => $numero],
                ['nombre' => $nombre],
            );
        }
    }
}
