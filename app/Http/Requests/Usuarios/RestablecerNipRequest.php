<?php

namespace App\Http\Requests\Usuarios;

use App\Rules\Nip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RestablecerNipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Recibe el NIP nuevo que el administrador le asigna al usuario.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nip' => ['required', new Nip],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nip.required' => 'El NIP es obligatorio.',
        ];
    }
}
