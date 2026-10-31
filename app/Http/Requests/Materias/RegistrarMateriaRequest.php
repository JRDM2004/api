<?php

namespace App\Http\Requests\Materias;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarMateriaRequest extends FormRequest
{
    // TODO: HU-11 T6 revisar con MateriaPolicy que solo el administrador pueda registrar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // TODO: HU-11 T5 validar que el semestre exista y que la clave no se repita.
        return [
            // Id del semestre al que pertenece la materia.
            'semestre_id' => ['required', 'integer'],
            // Clave de la materia en el plan de estudios. No se puede repetir.
            'clave' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'semestre_id.required' => 'El semestre es obligatorio.',
            'semestre_id.integer' => 'El semestre no es válido.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.max' => 'La clave no puede tener más de 20 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 150 caracteres.',
        ];
    }
}
