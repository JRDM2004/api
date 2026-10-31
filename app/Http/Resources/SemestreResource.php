<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de un semestre que salen en el JSON, con sus materias
 */
class SemestreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            // Solo sale si se cargaron las materias en la consulta.
            'materias' => MateriaResource::collection($this->whenLoaded('materias')),
        ];
    }
}
