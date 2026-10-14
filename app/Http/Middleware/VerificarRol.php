<?php

namespace App\Http\Middleware;

use App\Enums\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a los usuarios con alguno de los roles indicados en la ruta.
 *
 * Va después de auth:sanctum, que ya respondió 401 si no hay sesión.
 * Ejemplo: ->middleware(VerificarRol::class.':docente,administrador')
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $permitidos = array_map(fn (string $rol) => Rol::from($rol), $roles);

        if (! in_array($request->user()?->rol, $permitidos, true)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
