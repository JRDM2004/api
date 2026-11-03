<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de un curso que salen en el JSON
 */
class CursoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'materia_id' => $this->materia_id,
            'grupo_id' => $this->grupo_id,
            // Id del docente, o null si todavía no tiene.
            'docente_id' => $this->docente_id,
            // * El nombre y la clave del curso son los de su materia.
            'nombre' => $this->materia->nombre,
            'clave' => $this->materia->clave,
        ];
    }
}
