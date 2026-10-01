<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
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
}
