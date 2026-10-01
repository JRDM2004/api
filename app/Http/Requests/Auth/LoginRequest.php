<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero_cuenta' => trim((string) $this->numero_cuenta),
            'nip' => strtoupper(trim((string) $this->nip)),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_cuenta' => ['required', 'string', 'max:20'],
            'nip' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero_cuenta.required' => 'El numero de cuenta o matricula es obligatorio.',
            'nip.required' => 'El NIP es obligatorio.',
        ];
    }
}
