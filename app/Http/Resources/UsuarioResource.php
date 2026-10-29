<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de un usuario que salen en el JSON
 */
class UsuarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_cuenta' => $this->numero_cuenta,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'rol' => $this->rol->value,
            'activo' => $this->activo,
        ];
    }
}
