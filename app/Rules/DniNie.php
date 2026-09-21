<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * DNI (8 números + letra) o NIE (X/Y/Z + 7 números + letra) con letra de
 * control correcta. Espera el valor ya en mayúsculas y sin espacios ni guiones.
 */
class DniNie implements ValidationRule
{
    private const LETRAS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valor = strtoupper((string) $value);

        if (preg_match('/^(\d{8})([A-Z])$/', $valor, $m)) {
            $numero = $m[1];
            $letra = $m[2];
        } elseif (preg_match('/^([XYZ])(\d{7})([A-Z])$/', $valor, $m)) {
            $numero = strtr($m[1], ['X' => '0', 'Y' => '1', 'Z' => '2']).$m[2];
            $letra = $m[3];
        } else {
            $fail('El DNI/NIE no tiene un formato válido (8 números y letra, o X/Y/Z, 7 números y letra).');

            return;
        }

        if (self::LETRAS[(int) $numero % 23] !== $letra) {
            $fail('La letra del DNI/NIE no es correcta.');
        }
    }
}
