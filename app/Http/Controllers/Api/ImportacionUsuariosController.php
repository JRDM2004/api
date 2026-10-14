<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ImportarUsuariosRequest;
use App\Jobs\ProcesarImportacionUsuarios;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImportacionUsuariosController extends Controller
{
    /**
     * Recibe el archivo y lo procesa en segundo plano, porque si es un
     * archivo grande, puede tardar varios minutos.
     */
    public function store(ImportarUsuariosRequest $request): JsonResponse
    {
        $id = (string) Str::uuid();

        $ruta = $request->file('archivo')->storeAs('importaciones', "{$id}.csv");

        Cache::put(ProcesarImportacionUsuarios::claveCache($id), ['estado' => 'en_proceso'], now()->addDay());

        ProcesarImportacionUsuarios::dispatch($id, $ruta);

        return response()->json([
            'id' => $id,
            'estado' => 'en_proceso',
        ], 202);
    }

    /**
     * Devuelve el estado de la importación y, cuando termina, su reporte.
     */
    public function show(string $id): JsonResponse
    {
        $importacion = Cache::get(ProcesarImportacionUsuarios::claveCache($id));

        // El reporte solo se guarda 24 horas.
        if ($importacion === null) {
            return response()->json([
                'message' => 'La importación no existe o ya expiró.',
            ], 404);
        }

        return response()->json(['id' => $id, ...$importacion]);
    }
}
