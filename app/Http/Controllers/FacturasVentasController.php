<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacturaVenta;
use Illuminate\Support\Facades\Storage;

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
        if (!$factura->pdf_url || !Storage::disk('public')->exists($factura->pdf_url)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($factura->pdf_url),
            ['Content-Type' => 'application/pdf']
        );
    }
}
