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
        return $this->user()->can('update', $this->route('usuario'));
    }

    /**
     * Solo se editan nombre, correo y rol.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // bail: si una regla falla, no revisa las demás y da un solo mensaje.
        $reglasRol = ['bail', 'required', Rule::enum(Rol::class)];

        // * Un administrador no puede quitarse a sí mismo el rol de administrador.
        if ($this->route('usuario')->is($this->user())) {
            $reglasRol[] = Rule::in([Rol::Administrador->value]);
        }

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:150'],
            'rol' => $reglasRol,
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
            'rol.in' => 'No puedes quitarte el rol de administrador.',
        ];
    }
}
