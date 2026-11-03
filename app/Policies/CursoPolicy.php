<?php

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Usuario;

/**
 * Permisos para gestionar los cursos.
 *
 * $actor es quien hace la petición.
 */
class CursoPolicy
{
    public function create(Usuario $actor): bool
    {
        return $actor->rol === Rol::Administrador;
    }
}
