<?php

namespace App\Http\Requests\Grupos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultarAlumnosGrupoRequest extends FormRequest
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
            // Parte del número de cuenta o del nombre del alumno.
            'buscar' => ['nullable', 'string', 'max:100'],
            // Mandar 1 o true para ver también a los dados de baja.
            'incluir_bajas' => ['nullable', 'in:1,0,true,false'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede tener más de 100 caracteres.',
            'incluir_bajas.in' => 'incluir_bajas debe ser 1, 0, true o false.',
        ];
    }
}
