<?php

namespace App\Services\Facturas;

use App\Models\FacturaVenta;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class FacturaPdfService
{
    public function generar(FacturaVenta $factura): void
    {
        // Cargar relaciones necesarias
        $factura->load([
            'cliente',
            'detalles',
        ]);

        $pdf = Pdf::loadView('pdf.factura-venta', [
            'factura' => $factura,
            'empresa' => Empresa::first(),
        ])
            ->setPaper('A4')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        // Serie saneada para nombre de archivo
        $serieSafe = str_replace(['/', '\\'], '-', $factura->serie);

        $nombre = sprintf(
            'facturas/%s-%s.pdf',
            $serieSafe,
            str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT)
        );

        Storage::disk('public')->put($nombre, $pdf->output());

        $factura->update([
            'pdf_url' => $nombre,
        ]);
    }
}
