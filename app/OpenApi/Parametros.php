<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Parámetros de ruta que se repiten en varios endpoints de Swagger.
 *
 * ! Van en esta clase y no en AulaUasApi: el OA\Schema que llevan adentro
 * ! borraría los esquemas que están declarados en esa clase.
 */
#[OA\Parameter(
    parameter: 'UsuarioId',
    name: 'usuario',
    in: 'path',
    required: true,
    description: 'Id del usuario',
    schema: new OA\Schema(type: 'integer', example: 15),
)]
class Parametros
{
}
