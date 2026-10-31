<?php

namespace App\Http\Requests\Semestres;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarSemestresRequest extends FormRequest
{
    // TODO: HU-11 T6 revisar con MateriaPolicy que solo el administrador pueda consultar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Parte del nombre o de la clave de la materia. No distingue mayúsculas y minúsculas.
            'buscar' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede tener más de 100 caracteres.',
        ];
    }
}
