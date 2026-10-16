<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ActualizarUsuarioRequest;
use App\Http\Requests\Usuarios\CambiarEstadoUsuarioRequest;
use App\Http\Requests\Usuarios\ConsultarUsuariosRequest;
use App\Http\Requests\Usuarios\RegistrarUsuarioRequest;
use App\Http\Requests\Usuarios\RestablecerNipRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class UsuarioController extends Controller
{
    public const USUARIOS_POR_PAGINA = 15;

    /**
     * Lista los usuarios por páginas, ordenados por nombre.
     *
     * Acepta en la URL: buscar (número de cuenta o nombre), rol y estado
     * (activo o inactivo). Se pueden combinar.
     */
    #[OA\Get(
        path: '/api/usuarios',
        summary: 'Listar usuarios',
        description: 'Paginada de '.self::USUARIOS_POR_PAGINA.' en '.self::USUARIOS_POR_PAGINA
            .', ordenada por nombre. Los filtros se pueden combinar y se conservan en los enlaces de página.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buscar', in: 'query', required: false, description: 'Parte del número de cuenta o del nombre', schema: new OA\Schema(type: 'string', maxLength: 100)),
            new OA\Parameter(name: 'rol', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['alumno', 'docente', 'administrador'])),
            new OA\Parameter(name: 'estado', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['activo', 'inactivo'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Una página de usuarios',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Usuario')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/EnlacesPaginacion'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function index(ConsultarUsuariosRequest $request): AnonymousResourceCollection
    {
        $consulta = Usuario::query();

        if ($request->filled('buscar')) {
            $texto = '%'.$request->buscar.'%';

            $consulta->where(function ($grupo) use ($texto) {
                $grupo->whereLike('numero_cuenta', $texto)
                    ->orWhereLike('nombre', $texto);
            });
        }

        if ($request->filled('rol')) {
            $consulta->where('rol', $request->rol);
        }

        if ($request->filled('estado')) {
            $consulta->where('activo', $request->estado === 'activo');
        }

        $usuarios = $consulta->orderBy('nombre')->paginate(self::USUARIOS_POR_PAGINA);

        // Los enlaces de las otras páginas conservan la búsqueda y los filtros.
        $usuarios->withQueryString();

        return UsuarioResource::collection($usuarios);
    }

    /**
     * Registra un usuario. Queda activo y puede iniciar sesión de inmediato.
     */
    #[OA\Post(
        path: '/api/usuarios',
        summary: 'Registrar un usuario',
        description: 'El usuario queda activo y puede iniciar sesión de inmediato.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['numero_cuenta', 'nombre', 'rol', 'nip'],
                properties: [
                    new OA\Property(property: 'numero_cuenta', type: 'string', maxLength: 20, description: 'Único', example: '20231234'),
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Ana López'),
                    new OA\Property(property: 'rol', type: 'string', enum: ['alumno', 'docente', 'administrador'], example: 'alumno'),
                    new OA\Property(property: 'nip', type: 'string', minLength: 4, maxLength: 6, pattern: '^[A-Za-z0-9]{4,6}$', description: 'Distingue mayúsculas', example: 'Abc123'),
                    new OA\Property(property: 'correo', type: 'string', format: 'email', maxLength: 150, nullable: true, example: 'ana@uas.edu.mx'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado',
                content: new OA\JsonContent(ref: '#/components/schemas/MensajeConUsuario'),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function store(RegistrarUsuarioRequest $request): JsonResponse
    {
        $usuario = Usuario::create([
            'numero_cuenta' => $request->numero_cuenta,
            'nombre' => $request->nombre,
            'rol' => $request->rol,
            // * El cast "hashed" del modelo lo guarda con hash
            'nip_hash' => $request->nip,
            'correo' => $request->correo,
            'activo' => true,
        ]);

        return response()->json([
            'message' => 'Usuario registrado.',
            'user' => new UsuarioResource($usuario),
        ], 201);
    }

    /**
     * Edita nombre, correo y rol. El número de cuenta no se cambia.
     */
    #[OA\Put(
        path: '/api/usuarios/{usuario}',
        summary: 'Editar un usuario',
        description: 'Cambia nombre, correo y rol; el número de cuenta se ignora. '
            .'Un administrador no puede quitarse su propio rol de administrador (422).',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/UsuarioId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'rol'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 100, example: 'Ana López Ruiz'),
                    new OA\Property(property: 'correo', type: 'string', format: 'email', maxLength: 150, nullable: true, description: 'null lo quita', example: 'ana@uas.edu.mx'),
                    new OA\Property(property: 'rol', type: 'string', enum: ['alumno', 'docente', 'administrador'], example: 'docente'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/MensajeConUsuario')),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function update(ActualizarUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        $usuario->update([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'rol' => $request->rol,
        ]);

        return response()->json([
            'message' => 'Usuario actualizado.',
            'user' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Activa o desactiva la cuenta. No borra nada de su historial.
     */
    #[OA\Patch(
        path: '/api/usuarios/{usuario}/estado',
        summary: 'Activar o desactivar un usuario',
        description: 'Al desactivar se cierran todas sus sesiones. No borra nada de su historial. '
            .'Un administrador no puede desactivar su propia cuenta (422).',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/UsuarioId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['activo'],
                properties: [
                    new OA\Property(property: 'activo', type: 'boolean', description: 'true activa, false desactiva', example: false),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado cambiado',
                content: new OA\JsonContent(ref: '#/components/schemas/MensajeConUsuario'),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function cambiarEstado(CambiarEstadoUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        $activo = $request->boolean('activo');

        $usuario->update(['activo' => $activo]);

        // * Al desactivar se cierran todas sus sesiones abiertas.
        if (! $activo) {
            $usuario->tokens()->delete();
        }

        if ($activo) {
            $mensaje = 'Usuario activado.';
        } else {
            $mensaje = 'Usuario desactivado.';
        }

        return response()->json([
            'message' => $mensaje,
            'user' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Le asigna un NIP nuevo al usuario, para cuando olvida el suyo.
     */
    #[OA\Post(
        path: '/api/usuarios/{usuario}/restablecer-nip',
        summary: 'Restablecer el NIP de un usuario',
        description: 'Asigna un NIP nuevo, cierra todas las sesiones del usuario y lo registra en la bitácora '
            .'(sin guardar el NIP).',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/UsuarioId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nip'],
                properties: [
                    new OA\Property(property: 'nip', type: 'string', minLength: 4, maxLength: 6, pattern: '^[A-Za-z0-9]{4,6}$', description: 'Distingue mayúsculas', example: 'Nuevo1'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'NIP restablecido',
                content: new OA\JsonContent(ref: '#/components/schemas/MensajeConUsuario'),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/SinPermiso', response: 403),
            new OA\Response(ref: '#/components/responses/NoEncontrado', response: 404),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function restablecerNip(RestablecerNipRequest $request, Usuario $usuario): JsonResponse
    {
        // * Las tres cosas se guardan juntas: si una falla, no se guarda ninguna.
        DB::transaction(function () use ($request, $usuario) {
            // * El cast "hashed" del modelo lo guarda con hash
            $usuario->update(['nip_hash' => $request->nip]);

            // * Se cierran sus sesiones abiertas para que tenga que entrar con el NIP nuevo.
            $usuario->tokens()->delete();

            Bitacora::registrar($request->user(), Bitacora::NIP_RESTABLECIDO, $usuario);
        });

        // ! Nunca devolver el NIP en la respuesta
        return response()->json([
            'message' => 'NIP restablecido.',
            'user' => new UsuarioResource($usuario),
        ]);
    }
}
