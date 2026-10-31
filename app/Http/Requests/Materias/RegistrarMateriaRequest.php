<?php

namespace App\Http\Requests\Materias;

use App\Models\Materia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Materia::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // bail en semestre_id: si no es un número, no se busca en la base de datos.
        return [
            // Id del semestre al que pertenece la materia.
            'semestre_id' => ['bail', 'required', 'integer', 'exists:semestres,id'],
            // Clave de la materia en el plan de estudios. No se puede repetir.
            'clave' => ['required', 'string', 'max:20', 'unique:materias,clave'],
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
            'semestre_id.exists' => 'El semestre no existe.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.max' => 'La clave no puede tener más de 20 caracteres.',
            'clave.unique' => 'Ya existe una materia con esa clave.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 150 caracteres.',
        ];
    }
}
