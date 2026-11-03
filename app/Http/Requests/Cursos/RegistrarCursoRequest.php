<?php

namespace App\Http\Requests\Cursos;

use App\Models\Curso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegistrarCursoRequest extends FormRequest
{
    // TODO: HU-13 T7 revisar con CursoPolicy que solo el administrador pueda registrar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // bail: si no es un número, no se busca en la base de datos.
        return [
            // Id de la materia que se va a impartir.
            'materia_id' => ['bail', 'required', 'integer', 'exists:materias,id'],
            // Id del grupo que va a cursar la materia.
            'grupo_id' => ['bail', 'required', 'integer', 'exists:grupos,id'],
            // Id del docente. Es opcional al crear.
            'docente_id' => ['bail', 'nullable', 'integer', 'exists:usuarios,id'],
        ];
    }

    /**
     * Reglas que necesitan varios campos a la vez. Solo corren si los datos ya son válidos.
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

                // TODO: HU-13 T3 validar el semestre de la materia y el rol del docente.
                $yaExiste = Curso::where('materia_id', $this->materia_id)
                    ->where('grupo_id', $this->grupo_id)
                    ->exists();

                if ($yaExiste) {
                    $validator->errors()->add('materia_id', 'Esa materia ya tiene un curso en ese grupo.');
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
            'materia_id.required' => 'La materia es obligatoria.',
            'materia_id.integer' => 'La materia no es válida.',
            'materia_id.exists' => 'La materia no existe.',
            'grupo_id.required' => 'El grupo es obligatorio.',
            'grupo_id.integer' => 'El grupo no es válido.',
            'grupo_id.exists' => 'El grupo no existe.',
            'docente_id.integer' => 'El docente no es válido.',
            'docente_id.exists' => 'El docente no existe.',
        ];
    }
}
