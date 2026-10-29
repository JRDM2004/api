<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ImportarUsuariosRequest;
use App\Jobs\ProcesarImportacionUsuarios;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[Group('Importaciones', weight: 3)]
class ImportacionUsuariosController extends Controller
{
    /**
     * Importar usuarios desde un CSV
     *
     * El archivo se procesa en segundo plano porque, si es grande, puede
     * tardar varios minutos. Responde de inmediato (202) con un id; el
     * resultado se consulta con GET /api/usuarios/importaciones/{id}.
     * Encabezado del CSV: numero_cuenta,nombre,rol,nip,correo.
     */
    // * Si cambia ImportadorUsuarios::ENCABEZADOS, actualiza la descripción.
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
     * Consultar una importación
     *
     * Devuelve el estado (en_proceso, terminada o fallida). Mientras sea
     * en_proceso, vuelve a consultar cada pocos segundos. Si terminó trae
     * creados, omitidos (cuentas que ya existían) y errores (filas rechazadas
     * con su línea); si falló trae un mensaje. El resultado se guarda 24
     * horas; después responde 404.
     *
     * @param  string  $id  El id que devolvió la subida.
     */
    // * El contenido depende del estado, por eso se muestra un ejemplo de cada uno.
    #[Response(200, 'Estado de la importación', examples: [
        ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'en_proceso'],
        ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'terminada', 'creados' => 42, 'omitidos' => 3, 'errores' => ['Línea 7: El rol debe ser alumno, docente o administrador.']],
        ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'fallida', 'mensaje' => 'No se pudo procesar el archivo. Intenta subirlo de nuevo.'],
    ])]
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
