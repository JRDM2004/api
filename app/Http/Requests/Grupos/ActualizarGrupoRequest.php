<?php

namespace App\Http\Requests\Grupos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarGrupoRequest extends FormRequest
{
    // TODO: HU-11 T4 revisar con GrupoPolicy que solo el administrador pueda editar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Se editan semestre, nombre y turno.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // TODO: HU-11 T3 validar que el semestre exista, que el nombre no se repita (sin contar este grupo) y que el turno sea válido.
        // TODO: HU-13 un grupo con cursos no puede cambiar de semestre.
        return [
            // Id del semestre que cursa el grupo.
            'semestre_id' => ['required', 'integer'],
            // Nombre del grupo, por ejemplo 3-2. No se puede repetir.
            'nombre' => ['required', 'string', 'max:20'],
            // matutino o vespertino. Mandarlo en null quita el turno.
            'turno' => ['nullable', 'string'],
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
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres.',
        ];
    }
}
