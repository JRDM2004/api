<?php

namespace App\Http\Requests\Auth;

use App\Rules\Nip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CambiarNipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Pide el NIP actual, el nuevo y su confirmación (nip_confirmation).
     *
     * current_password compara contra el nip_hash del usuario de la sesión.
     * bail en nip: si una regla falla, no revisa las demás y da un solo mensaje.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // El NIP con el que inicias sesión hoy.
            'nip_actual' => ['required', 'string', 'current_password'],
            // El NIP nuevo: de 4 a 6 letras o números, distinto al actual. Repítelo en nip_confirmation.
            'nip' => ['bail', 'required', new Nip, 'confirmed', 'different:nip_actual'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nip_actual.required' => 'El NIP actual es obligatorio.',
            'nip_actual.current_password' => 'El NIP actual es incorrecto.',
            'nip.required' => 'El NIP nuevo es obligatorio.',
            'nip.confirmed' => 'La confirmación no coincide con el NIP nuevo.',
            'nip.different' => 'El NIP nuevo debe ser distinto al actual.',
        ];
    }
}
