<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de un alumno dentro de un grupo que salen en el JSON
 */
class GrupoAlumnoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'alumno_id' => $this->alumno_id,
            'numero_cuenta' => $this->alumno->numero_cuenta,
            'nombre' => $this->alumno->nombre,
            // activo o baja.
            'estado' => $this->estado,
            // Fecha de la baja, o null si sigue activo.
            'baja_en' => $this->baja_en,
        ];
    }
}
