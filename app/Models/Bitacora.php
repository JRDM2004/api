<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Una entrada de la bitácora de acciones sensibles.
 *
 * ! Nunca guardar el NIP aquí.
 */
#[Fillable(['actor_id', 'actor_nombre', 'actor_numero_cuenta', 'accion', 'objeto_tipo', 'objeto_id'])]
class Bitacora extends Model
{
    public const NIP_RESTABLECIDO = 'usuario.nip_restablecido';

    protected $table = 'bitacora';

    // Las entradas no se editan, así que no hay updated_at.
    public const UPDATED_AT = null;

    /**
     * Guarda una entrada con los datos de quien hizo la acción.
     */
    public static function registrar(Usuario $actor, string $accion, Usuario $objeto): self
    {
        return self::create([
            'actor_id' => $actor->id,
            'actor_nombre' => $actor->nombre,
            'actor_numero_cuenta' => $actor->numero_cuenta,
            'accion' => $accion,
            'objeto_tipo' => 'usuario',
            'objeto_id' => $objeto->id,
        ]);
    }
}
