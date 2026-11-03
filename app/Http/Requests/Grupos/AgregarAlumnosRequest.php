<?php

namespace App\Http\Requests\Grupos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AgregarAlumnosRequest extends FormRequest
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
        // El rol, el estado y el otro grupo se revisan en el controlador: esos alumnos se omiten, no rechazan la lista.
        // bail en cada id: si no es un número, no se busca en la base de datos.
        return [
            // Ids de los alumnos que se agregan al grupo, de 1 a 100 por petición.
            'alumno_ids' => ['required', 'array', 'min:1', 'max:100'],
            // Id de un alumno. No se puede repetir en la lista.
            'alumno_ids.*' => ['bail', 'integer', 'distinct', 'exists:usuarios,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alumno_ids.required' => 'La lista de alumnos es obligatoria.',
            'alumno_ids.array' => 'La lista de alumnos no es válida.',
            'alumno_ids.min' => 'Agrega al menos un alumno.',
            'alumno_ids.max' => 'No se pueden agregar más de 100 alumnos a la vez.',
            'alumno_ids.*.integer' => 'El alumno no es válido.',
            'alumno_ids.*.distinct' => 'Hay un alumno repetido en la lista.',
            'alumno_ids.*.exists' => 'El alumno no existe.',
        ];
    }
}
