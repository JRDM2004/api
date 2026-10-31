<?php

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Materia;
use App\Models\Usuario;

/**
 * Permisos para gestionar las materias del plan de estudios.
 *
 * $actor es quien hace la petición; $materia es la materia sobre la que actúa.
 * Por ahora todo es solo del administrador.
 */
class MateriaPolicy
{
    // * La lista de semestres con sus materias también usa este permiso.
    public function viewAny(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function create(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function update(Usuario $actor, Materia $materia): bool
    {
        return $this->esAdministrador($actor);
    }

    public function cambiarEstado(Usuario $actor, Materia $materia): bool
    {
        return $this->esAdministrador($actor);
    }

    private function esAdministrador(Usuario $actor): bool
    {
        return $actor->rol === Rol::Administrador;
    }
}
