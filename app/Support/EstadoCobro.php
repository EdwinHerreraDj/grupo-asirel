<?php

namespace App\Support;

/**
 * Estado INFORMATIVO de cobro / seguimiento.
 *
 * Capa de clasificación interna, SEPARADA de los estados operativos/fiscales
 * (factura: borrador/emitida/anulada; certificación: pendiente/aceptada).
 * No bloquea procesos fiscales, no sustituye estados, no toca importes,
 * numeración, PDFs ni VeriFactu.
 */
class EstadoCobro
{
    public const PENDIENTE      = 'pendiente';
    public const PAGADA         = 'pagada';
    public const CON_RETRASO    = 'con_retraso';
    public const EN_RECLAMACION = 'en_reclamacion';

    public const VALORES = [
        self::PENDIENTE,
        self::PAGADA,
        self::CON_RETRASO,
        self::EN_RECLAMACION,
    ];

    public const META = [
        self::PENDIENTE      => ['label' => 'Pendiente',      'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
        self::PAGADA         => ['label' => 'Pagada',         'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        self::CON_RETRASO    => ['label' => 'Con retraso',    'color' => 'bg-orange-100 text-orange-700 border-orange-200'],
        self::EN_RECLAMACION => ['label' => 'En reclamación', 'color' => 'bg-purple-100 text-purple-700 border-purple-200'],
    ];

    public static function esValido(?string $valor): bool
    {
        return in_array($valor, self::VALORES, true);
    }

    public static function meta(?string $valor): array
    {
        return self::META[$valor] ?? self::META[self::PENDIENTE];
    }

    public static function label(?string $valor): string
    {
        return self::meta($valor)['label'];
    }

    /** Opciones {value,label} para selects de UI (Livewire/React). */
    public static function opciones(): array
    {
        return array_map(
            fn ($v) => ['value' => $v, 'label' => self::META[$v]['label']],
            self::VALORES,
        );
    }
}
