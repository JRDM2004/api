<?php

namespace App\Http\Requests\Materias;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoMateriaRequest extends FormRequest
{
    // TODO: HU-11 T6 revisar con MateriaPolicy que solo el administrador pueda cambiar el estado.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Recibe el nuevo estado: true para activar y false para desactivar.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // bail: si una regla falla, no revisa las demás y da un solo mensaje.
            'activa' => ['bail', 'required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activa.required' => 'El estado es obligatorio.',
            'activa.boolean' => 'El estado debe ser true o false.',
        ];
    }
}
