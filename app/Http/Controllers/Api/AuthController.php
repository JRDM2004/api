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

class AuthController extends Controller
{
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
    public function miPerfil(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UsuarioResource($request->user()),
        ]);
    }

    /**
     * Cambia el NIP del usuario de la sesión. Pide el NIP actual.
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
     * Revoca solo el token con el que se hizo la peticion, asi que las
     * sesiones en otros dispositivos siguen abiertas.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada.',
        ]);
    }
}
