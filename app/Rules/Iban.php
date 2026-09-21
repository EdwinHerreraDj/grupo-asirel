<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * IBAN con dígitos de control válidos (ISO 13616, módulo 97). Espera el
 * valor en mayúsculas y sin espacios.
 */
class Iban implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $iban = strtoupper(preg_replace('/\s+/', '', (string) $value));

        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/', $iban)) {
            $fail('El IBAN no tiene un formato válido.');

            return;
        }

        if (str_starts_with($iban, 'ES') && strlen($iban) !== 24) {
            $fail('Un IBAN español debe tener 24 caracteres.');

            return;
        }

        $reordenado = substr($iban, 4).substr($iban, 0, 4);
        $numerico = preg_replace_callback('/[A-Z]/', fn ($m) => (string) (ord($m[0]) - 55), $reordenado);

        $resto = 0;
        foreach (str_split($numerico, 7) as $bloque) {
            $resto = (int) ($resto.$bloque) % 97;
        }

        if ($resto !== 1) {
            $fail('El IBAN no es correcto: revisa los dígitos.');
        }
    }
}
