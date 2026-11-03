<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de un grupo que salen en el JSON
 */
class GrupoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'semestre_id' => $this->semestre_id,
            'nombre' => $this->nombre,
            // matutino, vespertino o null si no tiene turno.
            'turno' => $this->turno,
            // Solo sale si se cargó el semestre en la consulta.
            'semestre' => new SemestreResource($this->whenLoaded('semestre')),
        ];
    }
}
