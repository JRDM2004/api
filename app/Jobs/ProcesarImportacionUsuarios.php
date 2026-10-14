<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Procesa en segundo plano un CSV de usuarios subido por el administrador.
 */
class ProcesarImportacionUsuarios implements ShouldQueue
{
    use Queueable;

    /**
     * Recibe el número de la importación y dónde quedó guardado el archivo.
     */
    public function __construct(
        public string $id,
        public string $ruta,
    ) {}

    /**
     * Nombre con el que se guardan en Redis el estado y el reporte.
     */
    public static function claveCache(string $id): string
    {
        return "importaciones:{$id}";
    }

    public function handle(): void
    {
        // TODO: procesar el archivo, guardar el reporte y borrar el archivo
    }
}
