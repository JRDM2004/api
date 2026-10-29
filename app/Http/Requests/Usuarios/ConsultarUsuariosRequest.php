<?php

namespace App\Http\Requests\Usuarios;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultarUsuariosRequest extends FormRequest
{
    // * Se revisa antes de validar: quien no tiene permiso recibe 403, no los errores de los datos.
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Usuario::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', Rule::enum(Rol::class)],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede tener más de 100 caracteres.',
            'rol.enum' => 'El rol debe ser alumno, docente o administrador.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ];
    }
}
