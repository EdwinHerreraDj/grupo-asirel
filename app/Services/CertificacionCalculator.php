<?php

namespace App\Services;

use App\Models\Certificacion;
use RuntimeException;

class CertificacionCalculator
{
    public function recalcular(Certificacion $certificacion): void
    {
        // Guardia: una certificaci\u00f3n facturada tiene sus importes congelados
        // en el pivote factura_venta_certificacion. Recalcularla aqu\u00ed divergir\u00eda
        // el valor de esta tabla con el del pivote = inconsistencia fiscal grave.
        if ($certificacion->estaFacturada()) {
            throw new RuntimeException(
                'No se puede recalcular una certificación ya facturada.'
            );
        }

        // 1. Base imponible = suma de l\u00edneas
        $base = $certificacion->detalles()->sum('importe_linea');

        // 2. IVA
        $ivaImporte = $base * ($certificacion->iva_porcentaje / 100);

        // 3. Retenci\u00f3n (IRPF)
        $retencionImporte = $base * ($certificacion->retencion_porcentaje / 100);

        // 4. Total final
        $total = $base + $ivaImporte - $retencionImporte;

        // 5. Guardar en certificaci\u00f3n
        $certificacion->update([
            'base_imponible'    => round($base, 2),
            'iva_importe'       => round($ivaImporte, 2),
            'retencion_importe' => round($retencionImporte, 2),
            'total'             => round($total, 2),
        ]);
    }
}
