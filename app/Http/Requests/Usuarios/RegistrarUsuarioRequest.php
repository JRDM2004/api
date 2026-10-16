<?php

namespace App\Http\Requests\Usuarios;

use App\Enums\Rol;
use App\Rules\Nip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarUsuarioRequest extends FormRequest
{
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
            'numero_cuenta' => ['required', 'string', 'max:20', 'unique:usuarios,numero_cuenta'],
            'nombre' => ['required', 'string', 'max:100'],
            'rol' => ['required', Rule::enum(Rol::class)],
            'nip' => ['required', new Nip],
            'correo' => ['nullable', 'email', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero_cuenta.required' => 'El número de cuenta o matrícula es obligatorio.',
            'numero_cuenta.max' => 'El número de cuenta no puede tener más de 20 caracteres.',
            'numero_cuenta.unique' => 'Ya existe un usuario con ese número de cuenta.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.enum' => 'El rol debe ser alumno, docente o administrador.',
            'nip.required' => 'El NIP es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede tener más de 150 caracteres.',
        ];
    }
}
