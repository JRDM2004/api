<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\RegistrarUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsuarioController extends Controller
{
    public const USUARIOS_POR_PAGINA = 15;

    /**
     * Lista los usuarios por páginas, ordenados por nombre.
     */
    public function index(): AnonymousResourceCollection
    {
        $usuarios = Usuario::orderBy('nombre')->paginate(self::USUARIOS_POR_PAGINA);

        return UsuarioResource::collection($usuarios);
    }

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
