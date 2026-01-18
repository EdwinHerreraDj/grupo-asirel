<?php

namespace App\Services;

use App\Models\FacturaRecibida;

class FacturaRecibidaCalculator
{
    public function recalcular(FacturaRecibida $factura): void
    {
        $base = (float) $factura->base_imponible;

        $ivaPorcentaje = (float) $factura->iva_porcentaje;
        $retencionPorcentaje = (float) $factura->retencion_porcentaje;

        $ivaImporte = round($base * ($ivaPorcentaje / 100), 2);
        $retencionImporte = round($base * ($retencionPorcentaje / 100), 2);

        $total = round(
            $base + $ivaImporte - $retencionImporte,
            2
        );

        $factura->update([
            'iva_importe'       => $ivaImporte,
            'retencion_importe' => $retencionImporte,
            'total'             => $total,
        ]);
    }
}
