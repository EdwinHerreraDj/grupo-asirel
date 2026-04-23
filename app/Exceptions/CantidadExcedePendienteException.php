<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lanzada cuando una l\u00ednea de certificaci\u00f3n intenta superar la medici\u00f3n
 * pendiente de su partida de venta. El controlador la captura y devuelve
 * un 409 Conflict con los datos necesarios para que la UI ofrezca al
 * usuario confirmar forzar el guardado.
 */
class CantidadExcedePendienteException extends RuntimeException
{
    public function __construct(
        public readonly float $pendiente,
        public readonly float $cantidadIntentada,
        string $mensaje = 'La cantidad supera la medición pendiente de la partida.',
    ) {
        parent::__construct($mensaje);
    }
}
