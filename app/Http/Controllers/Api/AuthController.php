<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CambiarNipRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        summary: 'Iniciar sesión',
        description: 'Devuelve un token para las demás rutas. El NIP distingue mayúsculas y minúsculas.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['numero_cuenta', 'nip'],
                properties: [
                    new OA\Property(property: 'numero_cuenta', type: 'string', maxLength: 20, example: '20231234'),
                    new OA\Property(property: 'nip', type: 'string', maxLength: 50, example: 'Abc123'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión iniciada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: '12|Xk9pR2...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/Usuario'),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Número de cuenta o NIP incorrectos (no dice cuál falló)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorMensaje'),
            ),
            new OA\Response(
                response: 403,
                description: 'La cuenta está desactivada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorMensaje'),
            ),
            new OA\Response(
                response: 422,
                description: 'Faltan datos',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidacion'),
            ),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::where('numero_cuenta', $request->numero_cuenta)->first();

        if (! $usuario || ! Hash::check($request->nip, $usuario->nip_hash)) {
            return response()->json([
                'message' => 'Número de cuenta o NIP incorrectos.',
            ], 401);
        }

        // Se revisa después del NIP para no revelar qué cuentas existen.
        if (! $usuario->activo) {
            return response()->json([
                'message' => 'Tu cuenta está desactivada. Contacta a soporte.',
            ], 403);
        }

        $expiraEn = now()->addMinutes((int) config('sanctum.expiration'));
        $token = $usuario->createToken('api', ['*'], $expiraEn);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiraEn->toIso8601String(),
            'user' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Devuelve el perfil del usuario de la sesión.
     *
     * * Solo da el perfil propio: no recibe id, usa el usuario del token.
     */
    #[OA\Get(
        path: '/api/mi-perfil',
        summary: 'Ver mi perfil',
        description: 'Datos del usuario dueño del token. Web y móvil también lo usan para saber qué menú mostrar.',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil del usuario',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/Usuario'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ],
    )]
    public function miPerfil(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UsuarioResource($request->user()),
        ]);
    }

    /**
     * Cambia el NIP del usuario de la sesión. Pide el NIP actual.
     */
    #[OA\Post(
        path: '/api/cambiar-nip',
        summary: 'Cambiar mi NIP',
        description: 'Pide el NIP actual y el nuevo dos veces. Cierra las demás sesiones del usuario; '
            .'la de este dispositivo sigue abierta.',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nip_actual', 'nip', 'nip_confirmation'],
                properties: [
                    new OA\Property(property: 'nip_actual', type: 'string', example: 'Abc123'),
                    new OA\Property(
                        property: 'nip',
                        type: 'string',
                        description: 'De 4 a 6 letras o números, distinto al actual. Distingue mayúsculas.',
                        minLength: 4,
                        maxLength: 6,
                        pattern: '^[A-Za-z0-9]{4,6}$',
                        example: 'Nuevo9',
                    ),
                    new OA\Property(property: 'nip_confirmation', type: 'string', description: 'Igual a nip.', example: 'Nuevo9'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'NIP cambiado',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Mensaje',
                    example: ['message' => 'NIP actualizado.'],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
            new OA\Response(ref: '#/components/responses/DatosInvalidos', response: 422),
        ],
    )]
    public function cambiarNip(CambiarNipRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $tokenActual = $usuario->currentAccessToken();

        // * Las dos cosas se guardan juntas: si una falla, no se guarda ninguna.
        DB::transaction(function () use ($usuario, $request, $tokenActual) {
            // * El cast "hashed" del modelo lo guarda con hash
            $usuario->update(['nip_hash' => $request->nip]);

            // * Cierra las demás sesiones; la de este dispositivo sigue abierta.
            $usuario->tokens()->where('id', '!=', $tokenActual->id)->delete();
        });

        return response()->json([
            'message' => 'NIP actualizado.',
        ]);
    }

    /**
     * Revoca solo el token con el que se hizo la peticion, asi que las
     * sesiones en otros dispositivos siguen abiertas.
     */
    #[OA\Post(
        path: '/api/logout',
        summary: 'Cerrar sesión',
        description: 'Revoca solo el token de esta petición; las sesiones de otros dispositivos siguen abiertas.',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Mensaje',
                    example: ['message' => 'Sesión cerrada.'],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NoAutenticado', response: 401),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada.',
        ]);
    }
}
