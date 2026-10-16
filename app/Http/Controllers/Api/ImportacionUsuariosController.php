<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ImportarUsuariosRequest;
use App\Jobs\ProcesarImportacionUsuarios;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ImportacionUsuariosController extends Controller
{
    /**
     * Recibe el archivo y lo procesa en segundo plano, porque si es un
     * archivo grande, puede tardar varios minutos.
     */
    // * Si cambia ImportadorUsuarios::ENCABEZADOS, actualiza la descripción.
    #[OA\Post(
        path: '/api/usuarios/importaciones',
        summary: 'Importar usuarios desde un CSV',
        description: 'Sube el archivo y lo procesa en segundo plano. Responde de inmediato con un id; '
            .'consulta el resultado con GET /api/usuarios/importaciones/{id}. '
            .'Encabezado del CSV: numero_cuenta,nombre,rol,nip,correo. '
            .'Peso máximo: '.ImportarUsuariosRequest::TAMANO_MAXIMO_KB.' KB.',
        tags: ['Importaciones'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['archivo'],
                    properties: [
                        new OA\Property(property: 'archivo', type: 'string', format: 'binary', description: 'Archivo .csv'),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: 'Archivo recibido; se está procesando',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'estado', type: 'string', enum: ['en_proceso']),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
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
    #[OA\Get(
        path: '/api/usuarios/importaciones/{id}',
        summary: 'Consultar una importación',
        description: 'Mientras el estado sea en_proceso, vuelve a consultar cada pocos segundos. '
            .'El resultado se guarda 24 horas.',
        tags: ['Importaciones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'El id que devolvió la subida', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado de la importación. Los demás campos dependen del estado.',
                content: new OA\JsonContent(
                    required: ['id', 'estado'],
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'estado', type: 'string', enum: ['en_proceso', 'terminada', 'fallida']),
                        new OA\Property(property: 'creados', type: 'integer', description: 'Solo si terminó'),
                        new OA\Property(property: 'omitidos', type: 'integer', description: 'Solo si terminó: cuentas que ya existían'),
                        new OA\Property(property: 'errores', type: 'array', items: new OA\Items(type: 'string'), description: 'Solo si terminó: filas rechazadas con su línea'),
                        new OA\Property(property: 'mensaje', type: 'string', description: 'Solo si falló'),
                    ],
                    examples: [
                        new OA\Examples(example: 'en_proceso', summary: 'Todavía procesando', value: ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'en_proceso']),
                        new OA\Examples(example: 'terminada', summary: 'Terminó', value: ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'terminada', 'creados' => 42, 'omitidos' => 3, 'errores' => ['Línea 7: El rol debe ser alumno, docente o administrador.']]),
                        new OA\Examples(example: 'fallida', summary: 'Falló', value: ['id' => '9b1c4f3e-2a7d-4c1e-8f0a-1d2e3f4a5b6c', 'estado' => 'fallida', 'mensaje' => 'No se pudo procesar el archivo. Intenta subirlo de nuevo.']),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(
                response: 404,
                description: 'No existe o ya pasaron las 24 horas',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorMensaje', example: ['message' => 'La importación no existe o ya expiró.']),
            ),
        ],
    )]
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
