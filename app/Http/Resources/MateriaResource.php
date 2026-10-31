<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de una materia que salen en el JSON
 */
class MateriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'semestre_id' => $this->semestre_id,
            'clave' => $this->clave,
            'nombre' => $this->nombre,
            'activa' => $this->activa,
        ];
    }
}
