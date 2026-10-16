<?php

namespace App\Http\Requests\Usuarios;

use App\Enums\Rol;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Solo se editan nombre, correo y rol.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:150'],
            'rol' => ['required', Rule::enum(Rol::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede tener más de 150 caracteres.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.enum' => 'El rol debe ser alumno, docente o administrador.',
        ];
    }
}
