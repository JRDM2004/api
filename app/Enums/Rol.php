<?php

namespace App\Enums;

/**
 * Si se agrega un rol, también hay que crear una migración que actualice la
 * columna usuarios.rol (lo verifica RolTest).
 */
enum Rol: string
{
    case Alumno = 'alumno';
    case Docente = 'docente';
    case Administrador = 'administrador';
}
