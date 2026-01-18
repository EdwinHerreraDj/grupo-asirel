<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacturaVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Empresa;

class FacturasVentasController extends Controller
{
    public function index()
    {
        return view('empresa.facturas_ventas.index');
    }

    public function detalle(FacturaVenta $factura)
    {
        return view('empresa.facturas_ventas.detalle', [
            'factura' => $factura,
        ]);
    }

    public function pdf(FacturaVenta $factura)
    {
        if ($factura->estado !== 'emitida') {
            abort(403);
        }

        $factura->load(['cliente', 'detalles']);

        $empresa = Empresa::first();

        $pdf = Pdf::loadView('pdf.factura-venta', [
            'factura' => $factura,
            'empresa' => $empresa,
        ])
            ->setPaper('A4')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        $nombre = sprintf(
            'facturas/%s-%s.pdf',
            $factura->serie,
            str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT)
        );

        Storage::disk('public')->put($nombre, $pdf->output());

        if (!$factura->pdf_url) {
            $factura->update(['pdf_url' => $nombre]);
        }

        return $pdf->stream($factura->serie . '-' . $factura->numero_factura . '.pdf');
    }
}
