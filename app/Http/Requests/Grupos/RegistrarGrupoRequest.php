<?php

namespace App\Http\Requests\Grupos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarGrupoRequest extends FormRequest
{
    // TODO: HU-11 T4 revisar con GrupoPolicy que solo el administrador pueda registrar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // TODO: HU-11 T3 validar que el semestre exista, que el nombre no se repita y que el turno sea válido.
        return [
            // Id del semestre que cursa el grupo.
            'semestre_id' => ['required', 'integer'],
            // Nombre del grupo, por ejemplo 3-2. No se puede repetir.
            'nombre' => ['required', 'string', 'max:20'],
            // matutino o vespertino. Es opcional.
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
