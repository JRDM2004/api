<?php

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Grupo;
use App\Models\Usuario;

/**
 * Permisos para gestionar los grupos.
 *
 * $actor es quien hace la petición; $grupo es el grupo sobre el que actúa.
 * Por ahora todo es solo del administrador.
 */
class GrupoPolicy
{
    public function viewAny(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function create(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function update(Usuario $actor, Grupo $grupo): bool
    {
        return $this->esAdministrador($actor);
    }

    private function esAdministrador(Usuario $actor): bool
    {
        return $actor->rol === Rol::Administrador;
    }
}
