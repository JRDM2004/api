<?php

namespace App\Jobs;

use App\Services\ImportadorUsuarios;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Procesa en segundo plano un CSV de usuarios subido por el administrador.
 */
class ProcesarImportacionUsuarios implements ShouldQueue
{
    use Queueable;

    // Hasta 15 minutos: cada usuario tarda un poco por el hash del NIP.
    public int $timeout = 900;

    // Si se pasa del tiempo, no lo vuelve a intentar.
    public bool $failOnTimeout = true;

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

    public function handle(ImportadorUsuarios $importador): void
    {
        $reporte = $importador->importar(Storage::path($this->ruta));

        Cache::put(self::claveCache($this->id), ['estado' => 'terminada', ...$reporte], now()->addDay());

        Storage::delete($this->ruta);
    }

    /**
     * Se ejecuta si el proceso falla después de todos sus intentos.
     */
    public function failed(?Throwable $error): void
    {
        Storage::delete($this->ruta);

        Cache::put(self::claveCache($this->id), [
            'estado' => 'fallida',
            'mensaje' => 'No se pudo procesar el archivo. Intenta subirlo de nuevo.',
        ], now()->addDay());
    }
}
