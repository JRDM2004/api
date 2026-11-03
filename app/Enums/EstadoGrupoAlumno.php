<?php

namespace App\Enums;

/**
 * Estado de un alumno dentro de un grupo. Si se agrega uno, también hay que
 * crear una migración que actualice la columna grupo_alumnos.estado.
 */
enum EstadoGrupoAlumno: string
{
    case Activo = 'activo';
    case Baja = 'baja';
}
