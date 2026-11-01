<?php

namespace App\Enums;

/**
 * Turno de un grupo. Si se agrega uno, también hay que crear una migración que
 * actualice la columna grupos.turno.
 */
enum Turno: string
{
    case Matutino = 'matutino';
    case Vespertino = 'vespertino';
}
