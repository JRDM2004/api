<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ConsultarUsuariosRequest;
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
     *
     * Acepta en la URL: buscar (número de cuenta o nombre), rol y estado
     * (activo o inactivo). Se pueden combinar.
     */
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
