<?php

namespace App\Http\Requests\Usuarios;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportarUsuariosRequest extends FormRequest
{
    public const TAMANO_MAXIMO_KB = 2048;

    public function authorize(): bool
    {
        return $this->user()->can('importar', Usuario::class);
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
