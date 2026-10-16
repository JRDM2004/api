<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ActualizarUsuarioRequest;
use App\Http\Requests\Usuarios\CambiarEstadoUsuarioRequest;
use App\Http\Requests\Usuarios\ConsultarUsuariosRequest;
use App\Http\Requests\Usuarios\RegistrarUsuarioRequest;
use App\Http\Requests\Usuarios\RestablecerNipRequest;
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

    /**
     * Edita nombre, correo y rol. El número de cuenta no se cambia.
     */
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
    public function restablecerNip(RestablecerNipRequest $request, Usuario $usuario): JsonResponse
    {
        // * El cast "hashed" del modelo lo guarda con hash
        $usuario->update(['nip_hash' => $request->nip]);

        // ! Nunca devolver el NIP en la respuesta
        return response()->json([
            'message' => 'NIP restablecido.',
            'user' => new UsuarioResource($usuario),
        ]);
    }
}
