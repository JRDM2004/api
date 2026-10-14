<?php

namespace App\Http\Requests\Usuarios;

use App\Enums\Rol;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportarUsuariosRequest extends FormRequest
{
    public const TAMANO_MAXIMO_KB = 2048;

    // TODO: HU-03 cambiar esta revisión por el middleware de roles
    public function authorize(): bool
    {
        return $this->user()?->rol === Rol::Administrador;
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Solo el administrador puede importar usuarios.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'extensions:csv', 'mimes:csv,txt', 'max:'.self::TAMANO_MAXIMO_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Selecciona el archivo CSV que quieres importar.',
            'archivo.file' => 'El archivo no se subió correctamente.',
            'archivo.extensions' => 'El archivo debe ser .csv.',
            'archivo.mimes' => 'El archivo debe ser un CSV de texto.',
            'archivo.max' => 'El archivo no puede pesar más de 2 MB.',
        ];
    }
}
