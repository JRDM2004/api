<?php

namespace App\Http\Requests\Grupos;

use App\Models\Grupo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarGruposRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Grupo::class);
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
