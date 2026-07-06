<?php

namespace App\Services\facturas;

use App\Models\Empresa;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaReimpresion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Generación del PDF de factura de venta, distinguiendo (VeriFactu):
 *
 *  - ORIGINAL: representación histórica creada en la emisión. Inmutable: una
 *    vez congelada NO se sobrescribe aunque cambie logo/colores/plantilla.
 *  - COPIA / reimpresión: representación visual POSTERIOR, generada al vuelo a
 *    partir de los datos fiscales ya congelados de la factura. Puede reflejar
 *    el branding actual. No modifica la factura ni el original.
 */
class FacturaPdfService
{
    /**
     * Genera y PERSISTE el PDF ORIGINAL. Se llama en la emisión.
     *
     * Protección: si el original ya está congelado (factura no-borrador con
     * `pdf_original_generado_at`), no hace nada. Así una llamada accidental
     * nunca sobrescribe el documento histórico.
     */
    public function generarOriginal(FacturaVenta $factura): void
    {
        if ($factura->pdfOriginalEsInmutable()) {
            return;
        }

        $factura->load(['cliente', 'detalles']);

        $pdf = $this->render($factura, esCopia: false);

        $serieSafe = str_replace(['/', '\\'], '-', $factura->serie);
        $nombre = sprintf(
            'facturas/%s-%s.pdf',
            $serieSafe,
            str_pad($factura->numero_factura, 6, '0', STR_PAD_LEFT)
        );

        Storage::disk('public')->put($nombre, $pdf->output());

        $factura->update([
            'pdf_url'                  => $nombre,
            'pdf_original_generado_at' => now(),
        ]);
    }

    /**
     * Alias retrocompatible: emisión = generar original.
     *
     * @deprecated Usa generarOriginal().
     */
    public function generar(FacturaVenta $factura): void
    {
        $this->generarOriginal($factura);
    }

    /**
     * Genera una COPIA / reimpresión al vuelo desde los datos congelados de la
     * factura y devuelve los bytes del PDF. NO persiste archivo, NO toca la
     * factura ni el original; solo deja rastro de auditoría de la reimpresión.
     */
    public function generarCopia(FacturaVenta $factura): string
    {
        $factura->load(['cliente', 'detalles']);

        $pdf = $this->render($factura, esCopia: true);
        $bytes = $pdf->output();

        FacturaVentaReimpresion::create([
            'factura_venta_id' => $factura->id,
            'user_id'          => Auth::id(),
            'tipo'             => FacturaVentaReimpresion::TIPO_REIMPRESION,
        ]);

        return $bytes;
    }

    /**
     * Render común. `$esCopia` marca visualmente el documento como copia para
     * no confundirlo con el original fiscal.
     */
    private function render(FacturaVenta $factura, bool $esCopia)
    {
        return Pdf::loadView('pdf.factura-venta', [
            'factura'    => $factura,
            'empresa'    => Empresa::first(),
            'esCopia'    => $esCopia,
            'fechaCopia' => $esCopia ? now() : null,
        ])
            ->setPaper('A4')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);
    }
}
