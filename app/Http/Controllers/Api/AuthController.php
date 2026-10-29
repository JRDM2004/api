<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CambiarNipRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Group('Autenticación', weight: 1)]
class AuthController extends Controller
{
    /**
     * Iniciar sesión
     *
     * Devuelve un token para usar en las demás rutas. El NIP distingue
     * mayúsculas y minúsculas. Si el número de cuenta o el NIP están mal
     * responde 401 sin decir cuál falló; si la cuenta está desactivada, 403.
     *
     * @unauthenticated
     */
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
     * Ver mi perfil
     *
     * Datos del usuario dueño del token. No recibe id: solo da el perfil
     * propio. Web y móvil lo usan para saber qué menú mostrar.
     */
    public function miPerfil(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UsuarioResource($request->user()),
        ]);
    }

    /**
     * Cambiar mi NIP
     *
     * Pide el NIP actual y el nuevo dos veces. El nuevo debe tener de 4 a 6
     * letras o números y ser distinto al actual. Cierra las demás sesiones
     * del usuario; la de este dispositivo sigue abierta.
     */
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
     * Cerrar sesión
     *
     * Revoca solo el token de esta petición, así que las sesiones en otros
     * dispositivos siguen abiertas.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada.',
        ]);
    }
}
