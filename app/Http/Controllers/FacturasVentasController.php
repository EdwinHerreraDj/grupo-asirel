<?php

namespace App\Http\Controllers;

use App\Models\FacturaVenta;
use App\Models\FacturaVentaDocumento;
use App\Services\facturas\FacturaPdfService;
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

    /**
     * PDF ORIGINAL histórico (el archivo congelado en la emisión).
     * Nunca se regenera aquí: se sirve tal cual se emitió.
     */
    public function pdf(FacturaVenta $factura)
    {
        if (! $factura->pdf_url || ! Storage::disk('public')->exists($factura->pdf_url)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($factura->pdf_url),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * COPIA / reimpresión: se renderiza al vuelo desde los datos congelados de
     * la factura con el branding ACTUAL. No altera la factura ni el original;
     * queda registrada para trazabilidad.
     */
    public function pdfCopia(FacturaVenta $factura, FacturaPdfService $pdfService)
    {
        if (! $factura->puedeGenerarCopia()) {
            abort(422, 'Solo se pueden reimprimir facturas ya emitidas.');
        }

        $bytes = $pdfService->generarCopia($factura);

        $nombre = sprintf(
            'Factura_%s-%s_COPIA.pdf',
            str_replace(['/', '\\'], '-', $factura->serie),
            $factura->numero_factura ?? 'BORRADOR'
        );

        return response()->streamDownload(
            fn () => print($bytes),
            $nombre,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Descarga un documento adjunto (soporte documental) con su nombre
     * original. Verifica que pertenezca a la factura de la URL.
     */
    public function documentoDescargar(FacturaVenta $factura, FacturaVentaDocumento $documento)
    {
        abort_unless($documento->factura_venta_id === $factura->id, 404);

        if (! Storage::disk('public')->exists($documento->ruta)) {
            abort(404);
        }

        return Storage::disk('public')->download($documento->ruta, $documento->nombre_original);
    }
}
