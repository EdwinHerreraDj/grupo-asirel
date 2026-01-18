<?php

namespace App\Services;

use App\Models\Certificacion;

class CertificacionCalculator
{
    public function recalcular(Certificacion $certificacion): void
    {
        // 1. Base imponible = suma de líneas
        $base = $certificacion->detalles()->sum('importe_linea');

        // 2. IVA
        $ivaImporte = $base * ($certificacion->iva_porcentaje / 100);

        // 3. Retención (IRPF)
        $retencionImporte = $base * ($certificacion->retencion_porcentaje / 100);

        // 4. Total final
        $total = $base + $ivaImporte - $retencionImporte;

        // 5. Guardar en certificación
        $certificacion->update([
            'base_imponible'       => round($base, 2),
            'iva_importe'          => round($ivaImporte, 2),
            'retencion_importe'    => round($retencionImporte, 2),
            'total'                => round($total, 2),
        ]);
    }
}
