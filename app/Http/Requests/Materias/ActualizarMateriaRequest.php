<?php

namespace App\Http\Requests\Materias;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('materia'));
    }

    /**
     * Se editan semestre, clave y nombre. El estado se cambia en su propio endpoint.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
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
     * Reglas que necesitan consultar otros datos. Solo corren si los campos ya son válidos.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $materia = $this->route('materia');
                // (int) porque la validación también acepta "5" como texto.
                $cambiaDeSemestre = (int) $this->semestre_id !== $materia->semestre_id;

                // * Sus cursos dejarían de coincidir con el semestre de su grupo.
                if ($cambiaDeSemestre && $materia->cursos()->exists()) {
                    $validator->errors()->add('semestre_id', 'La materia tiene cursos: no puede cambiar de semestre.');
                }
            },
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
