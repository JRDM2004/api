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
     * Recibe el archivo y lo procesa en segundo plano, porque puede tardar
     * varios minutos.
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
}
