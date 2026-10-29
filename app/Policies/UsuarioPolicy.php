<?php

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Usuario;

/**
 * Permisos para gestionar usuarios.
 *
 * $actor es quien hace la petición; $usuario es el usuario sobre el que actúa.
 * Por ahora todo es solo del administrador.
 */
class UsuarioPolicy
{
    public function viewAny(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function create(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    public function update(Usuario $actor, Usuario $usuario): bool
    {
        return $this->esAdministrador($actor);
    }

    // * Que el admin no se desactive a sí mismo lo revisa la validación (422), no aquí
    public function cambiarEstado(Usuario $actor, Usuario $usuario): bool
    {
        return $this->esAdministrador($actor);
    }

    public function restablecerNip(Usuario $actor, Usuario $usuario): bool
    {
        return $this->esAdministrador($actor);
    }

    /**
     * Importar usuarios desde un CSV y consultar cómo va la importación.
     * No recibe un usuario porque la importación no tiene modelo propio.
     */
    public function importar(Usuario $actor): bool
    {
        return $this->esAdministrador($actor);
    }

    private function esAdministrador(Usuario $actor): bool
    {
        return $actor->rol === Rol::Administrador;
    }
}
