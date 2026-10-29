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
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Usuarios', weight: 2)]
class UsuarioController extends Controller
{
    public const USUARIOS_POR_PAGINA = 15;

    /**
     * Listar usuarios
     *
     * Paginada de 15 en 15 y ordenada por nombre. Acepta en la URL: buscar
     * (parte del número de cuenta o del nombre), rol y estado (activo o
     * inactivo). Los filtros se pueden combinar y se conservan en los
     * enlaces de página.
     */
    // * La paginación no la detecta sola la documentación, por eso se declara "page".
    #[QueryParameter('page', 'Número de página.', type: 'int', default: 1)]
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
     * Registrar un usuario
     *
     * El usuario queda activo y puede iniciar sesión de inmediato. El número
     * de cuenta no se puede repetir y el NIP (de 4 a 6 letras o números)
     * distingue mayúsculas y minúsculas.
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
     * Editar un usuario
     *
     * Cambia nombre, correo y rol; el número de cuenta se ignora. Mandar el
     * correo en null lo quita. Un administrador no puede quitarse su propio
     * rol de administrador (422).
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
     * Activar o desactivar un usuario
     *
     * activo en true activa la cuenta y en false la desactiva. Al desactivar
     * se cierran todas sus sesiones; no se borra nada de su historial. El
     * mensaje dice "Usuario activado." o "Usuario desactivado.". Un
     * administrador no puede desactivar su propia cuenta (422).
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
     * Restablecer el NIP de un usuario
     *
     * Para cuando el usuario olvida su NIP. Asigna uno nuevo (de 4 a 6 letras
     * o números), cierra todas sus sesiones y lo registra en la bitácora sin
     * guardar el NIP.
     */
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
