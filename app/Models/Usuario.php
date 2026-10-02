<?php

namespace App\Models;

use App\Enums\Rol;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['numero_cuenta', 'nombre', 'correo', 'nip_hash', 'rol', 'telefono', 'foto_ruta', 'activo'])]
#[Hidden(['nip_hash'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasApiTokens, HasFactory;

    protected $table = 'usuarios';

    /**
     * Columna que Laravel usa como contraseña.
     */
    protected $authPasswordName = 'nip_hash';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nip_hash' => 'hashed',
            'rol' => Rol::class,
            'activo' => 'boolean',
        ];
    }
}
