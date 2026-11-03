<?php

namespace App\Http\Requests\Cursos;

use App\Enums\Rol;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Usuario;
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

                $materia = Materia::find($this->materia_id);
                $grupo = Grupo::find($this->grupo_id);

                if (! $materia->activa) {
                    $validator->errors()->add('materia_id', 'La materia no está activa.');
                }

                // * Un grupo solo cursa materias de su propio semestre.
                if ($materia->semestre_id !== $grupo->semestre_id) {
                    $validator->errors()->add('materia_id', 'La materia no pertenece al semestre del grupo.');
                }

                if ($this->filled('docente_id')) {
                    $docente = Usuario::find($this->docente_id);

                    if ($docente->rol !== Rol::Docente) {
                        $validator->errors()->add('docente_id', 'El usuario no tiene rol de docente.');
                    } elseif (! $docente->activo) {
                        $validator->errors()->add('docente_id', 'El docente no está activo.');
                    }
                }

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
