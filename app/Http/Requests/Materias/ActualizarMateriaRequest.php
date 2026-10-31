<?php

namespace App\Http\Requests\Materias;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarMateriaRequest extends FormRequest
{
    // TODO: HU-11 T6 revisar con MateriaPolicy que solo el administrador pueda editar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Se editan semestre, clave y nombre. El estado se cambia en su propio endpoint.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // TODO: HU-15 una materia con cursos no puede cambiar de semestre.
        // bail en semestre_id: si no es un número, no se busca en la base de datos.
        return [
            // Id del semestre al que pertenece la materia.
            'semestre_id' => ['bail', 'required', 'integer', 'exists:semestres,id'],
            // Clave de la materia en el plan de estudios. No se puede repetir.
            // * ignore(): la materia puede conservar su propia clave al editarse.
            'clave' => ['required', 'string', 'max:20', Rule::unique('materias', 'clave')->ignore($this->route('materia'))],
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
