<?php

namespace App\Http\Requests\Grupos;

use App\Enums\EstadoGrupoAlumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gestionarAlumnos', $this->route('grupo'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // baja para darlo de baja, activo para que reingrese.
            'estado' => ['required', Rule::enum(EstadoGrupoAlumno::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.enum' => 'El estado debe ser activo o baja.',
        ];
    }
}
