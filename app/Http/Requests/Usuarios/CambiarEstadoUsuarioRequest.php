<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cambiarEstado', $this->route('usuario'));
    }

    /**
     * Recibe el nuevo estado: true para activar y false para desactivar.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // bail: si una regla falla, no revisa las demás y da un solo mensaje.
        $reglasActivo = ['bail', 'required', 'boolean'];

        // * Un administrador no puede desactivar su propia cuenta.
        if ($this->route('usuario')->is($this->user())) {
            $reglasActivo[] = 'accepted';
        }

        return [
            'activo' => $reglasActivo,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activo.required' => 'El estado es obligatorio.',
            'activo.boolean' => 'El estado debe ser true o false.',
            'activo.accepted' => 'No puedes desactivar tu propia cuenta.',
        ];
    }
}
