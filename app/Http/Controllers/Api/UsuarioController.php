<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\RegistrarUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    /**
     * Registra un usuario. Queda activo y puede iniciar sesión de inmediato.
     */
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
}
