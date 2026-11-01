<?php

namespace App\Http\Requests\Grupos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarGruposRequest extends FormRequest
{
    // TODO: HU-11 T4 revisar con GrupoPolicy que solo el administrador pueda consultar.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Id del semestre para ver solo sus grupos. Sin él salen todos.
            'semestre_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'semestre_id.integer' => 'El semestre no es válido.',
        ];
    }
}
