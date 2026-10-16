<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Formato del NIP: de 4 a 6 caracteres, solo letras y números.
 *
 */
class Nip implements ValidationRule
{
    public const MIN_LENGTH = 4;
    public const MAX_LENGTH = 6;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = '/^[A-Za-z0-9]{'.self::MIN_LENGTH.','.self::MAX_LENGTH.'}$/';

        if (! is_string($value) || ! preg_match($pattern, $value)) {
            $fail('El NIP debe tener de '.self::MIN_LENGTH.' a '.self::MAX_LENGTH.' caracteres, solo letras y números.');
        }
    }
}
