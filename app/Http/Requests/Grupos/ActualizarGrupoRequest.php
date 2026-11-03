<?php

namespace App\Http\Requests\Grupos;

use App\Enums\Turno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('grupo'));
    }

    /**
     * Se editan semestre, nombre y turno.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // bail en semestre_id: si no es un número, no se busca en la base de datos.
        return [
            // Id del semestre que cursa el grupo.
            'semestre_id' => ['bail', 'required', 'integer', 'exists:semestres,id'],
            // Nombre del grupo, por ejemplo 3-2. No se puede repetir.
            // * ignore(): el grupo puede conservar su propio nombre al editarse.
            'nombre' => ['required', 'string', 'max:20', Rule::unique('grupos', 'nombre')->ignore($this->route('grupo'))],
            // matutino o vespertino. Mandarlo en null quita el turno.
            'turno' => ['nullable', Rule::enum(Turno::class)],
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

                $grupo = $this->route('grupo');
                // (int) porque la validación también acepta "5" como texto.
                $cambiaDeSemestre = (int) $this->semestre_id !== $grupo->semestre_id;

                // * Sus cursos dejarían de coincidir con el semestre de sus materias.
                if ($cambiaDeSemestre && $grupo->cursos()->exists()) {
                    $validator->errors()->add('semestre_id', 'El grupo tiene cursos: no puede cambiar de semestre.');
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
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres.',
            'nombre.unique' => 'Ya existe un grupo con ese nombre.',
            'turno.enum' => 'El turno debe ser matutino o vespertino.',
        ];
    }
}
