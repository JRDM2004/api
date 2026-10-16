<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Datos de un usuario que salen en el JSON
 */
// * Si cambian los campos de toArray(), actualiza también este esquema.
#[OA\Schema(
    schema: 'Usuario',
    required: ['id', 'numero_cuenta', 'nombre', 'correo', 'rol', 'activo'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 15),
        new OA\Property(property: 'numero_cuenta', type: 'string', example: '20231234'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Ana López'),
        new OA\Property(property: 'correo', type: 'string', format: 'email', nullable: true, example: 'ana@uas.edu.mx'),
        new OA\Property(property: 'rol', type: 'string', enum: ['alumno', 'docente', 'administrador'], example: 'alumno'),
        new OA\Property(property: 'activo', type: 'boolean', example: true),
    ],
)]
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
