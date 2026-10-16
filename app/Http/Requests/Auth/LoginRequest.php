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
            'numero_cuenta' => $this->limpiar($this->numero_cuenta),
            // ! El NIP distingue mayúsculas y minúsculas: no cambiarlas aquí
            'nip' => $this->limpiar($this->nip),
        ]);
    }

    /**
     * Quita los espacios de los lados. Si no es texto ni número (por ejemplo,
     * un arreglo), lo deja igual para que la validación lo rechace con un 422.
     */
    private function limpiar(mixed $valor): mixed
    {
        // ! Convertir un arreglo a texto truena PHP (error 500)
        if (! is_scalar($valor)) {
            return $valor;
        }

        return trim((string) $valor);
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
            'numero_cuenta.string' => 'El número de cuenta no es válido.',
            'nip.required' => 'El NIP es obligatorio.',
            'nip.string' => 'El NIP no es válido.',
        ];
    }
}
