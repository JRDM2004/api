<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoUsuarioRequest extends FormRequest
{
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
            'activo' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activo.required' => 'El estado es obligatorio.',
            'activo.boolean' => 'El estado debe ser true o false.',
        ];
    }
}
