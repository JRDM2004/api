<?php

namespace App\Http\Requests\Semestres;

use App\Models\Materia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarSemestresRequest extends FormRequest
{
    // * Lo que se consulta son las materias, por eso el permiso es de MateriaPolicy.
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Materia::class);
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
