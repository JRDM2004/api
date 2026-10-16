<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Datos generales de la documentación de Swagger.
 *
 * La clase está vacía a propósito: solo sirve para colgar los atributos que
 * describen a toda la API. Cada endpoint se documenta en su controlador.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'Aula UAS API',
    description: 'API del LMS de la facultad. Las rutas protegidas piden el token '
        .'que devuelve POST /api/login: presiona "Authorize" y pégalo.',
)]
// * URL relativa: funciona igual en local que en el servidor de pruebas.
#[OA\Server(url: '/', description: 'Servidor actual')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    description: 'Token de Laravel Sanctum, sin la palabra Bearer.',
)]
#[OA\Tag(name: 'Autenticación', description: 'Inicio de sesión, perfil y NIP propio')]
#[OA\Tag(name: 'Usuarios', description: 'Gestión de usuarios (solo administrador)')]
#[OA\Tag(name: 'Importaciones', description: 'Carga masiva de usuarios con CSV (solo administrador)')]
#[OA\Schema(
    schema: 'Mensaje',
    description: 'Respuesta de éxito que solo trae un mensaje.',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada.'),
    ],
)]
#[OA\Schema(
    schema: 'MensajeConUsuario',
    description: 'Respuesta de éxito con un mensaje y el usuario afectado.',
    required: ['message', 'user'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Usuario actualizado.'),
        new OA\Property(property: 'user', ref: '#/components/schemas/Usuario'),
    ],
)]
// * Paginación de Laravel: la usan todas las listas con ->paginate().
#[OA\Schema(
    schema: 'EnlacesPaginacion',
    properties: [
        new OA\Property(property: 'first', type: 'string', format: 'uri'),
        new OA\Property(property: 'last', type: 'string', format: 'uri'),
        new OA\Property(property: 'prev', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'next', type: 'string', format: 'uri', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'MetaPaginacion',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 3),
        new OA\Property(property: 'path', type: 'string', format: 'uri'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'to', type: 'integer', nullable: true, example: 15),
        new OA\Property(property: 'total', type: 'integer', example: 45),
        new OA\Property(
            property: 'links',
            type: 'array',
            description: 'Botones de página listos para dibujar.',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'label', type: 'string', example: '2'),
                    new OA\Property(property: 'page', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'active', type: 'boolean'),
                ],
            ),
        ),
    ],
)]
// Respuestas de error que se repiten en varios endpoints.
#[OA\Schema(
    schema: 'ErrorMensaje',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'No tienes permiso para realizar esta acción.'),
    ],
)]
#[OA\Schema(
    schema: 'ErrorValidacion',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'El NIP es obligatorio.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Cada campo con su lista de errores.',
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
            example: ['nip' => ['El NIP es obligatorio.']],
        ),
    ],
)]
// Respuestas que se repiten: cada endpoint las usa con ref.
#[OA\Response(
    response: 'NoAutenticado',
    description: 'Sin token, o el token venció o fue revocado',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorMensaje',
        example: ['message' => 'Tu sesión no es valida o expiro. Inicia sesión de nuevo.'],
    ),
)]
#[OA\Response(
    response: 'SinPermiso',
    description: 'El rol del usuario no tiene permiso',
    content: new OA\JsonContent(ref: '#/components/schemas/ErrorMensaje'),
)]
#[OA\Response(
    response: 'NoEncontrado',
    description: 'El registro o la ruta no existe',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorMensaje',
        example: ['message' => 'No se encontró el recurso.'],
    ),
)]
#[OA\Response(
    response: 'DatosInvalidos',
    description: 'Los datos enviados no pasan la validación',
    content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidacion'),
)]
class AulaUasApi
{
}
