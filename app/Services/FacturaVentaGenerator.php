<?php

namespace App\Services;

use App\Models\Certificacion;
use App\Models\CertificacionEvento;
use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\Obra;
use App\Services\Facturas\FacturaPdfService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Emite facturas de venta a partir de un grupo de certificaciones que
 * comparten `numero_certificacion`.
 *
 * Invariantes (AI_CONTEXT):
 *  - Todos los cap\u00edtulos del n\u00famero deben estar aceptados y pendientes de factura.
 *  - Mismo cliente, mismo IVA y misma retenci\u00f3n entre cap\u00edtulos.
 *  - Se crea una \u00fanica factura por el grupo.
 *  - Una l\u00ednea por cap\u00edtulo (no se copian l\u00edneas t\u00e9cnicas), con precio = base_imponible.
 *  - Transacci\u00f3n con lockForUpdate sobre certificaciones y serie.
 */
class FacturaVentaGenerator
{
    public function __construct(
        private readonly FacturaPdfService $pdfService,
    ) {}

    /**
     * Emite la factura y genera su PDF. Todo dentro de una transacci\u00f3n:
     * si el PDF falla, la factura se revierte.
     */
    public function emitirDesdeCertificaciones(
        Obra $obra,
        string $numeroCertificacion,
        FacturaSerie $serie,
    ): FacturaVenta {
        return DB::transaction(function () use ($obra, $numeroCertificacion, $serie) {
            $certs = $this->cargarCertificacionesConLock($obra, $numeroCertificacion);

            $this->validarInvariantes($certs, $obra, $numeroCertificacion);

            $serie = $this->bloquearYAvanzarSerie($serie);

            $factura = $this->crearFactura(
                obra: $obra,
                numeroCertificacion: $numeroCertificacion,
                serie: $serie,
                certs: $certs,
            );

            foreach ($certs as $cert) {
                $this->crearLineaFactura($factura, $cert);
            }

            $this->marcarCertificacionesComoFacturadas($obra, $numeroCertificacion);

            $this->registrarEventoFacturada($certs, $factura);

            $this->pdfService->generar($factura);

            return $factura;
        });
    }

    /**
     * Devuelve los grupos facturables de una obra, ya formateados para UI.
     * \u00datil para el endpoint `facturables` sin duplicar query.
     */
    public function obtenerFacturables(Obra $obra): array
    {
        return Certificacion::select(
            'numero_certificacion',
            'cliente_id',
            DB::raw('SUM(base_imponible) as base'),
            DB::raw('SUM(total) as total'),
            DB::raw('COUNT(*) as total_capitulos'),
        )
            ->where('obra_id', $obra->id)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->groupBy('numero_certificacion', 'cliente_id')
            ->with('cliente')
            ->orderBy('numero_certificacion', 'desc')
            ->get()
            ->map(fn ($g) => [
                'numero_certificacion' => $g->numero_certificacion,
                'cliente_nombre'       => $g->cliente->nombre ?? '—',
                'base'                 => (float) $g->base,
                'total'                => (float) $g->total,
                'total_capitulos'      => (int) $g->total_capitulos,
            ])
            ->toArray();
    }

    // -------------------------------------------------------------
    // INTERNOS
    // -------------------------------------------------------------

    private function cargarCertificacionesConLock(Obra $obra, string $numero): Collection
    {
        return Certificacion::where('obra_id', $obra->id)
            ->where('numero_certificacion', $numero)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->with(['oficio', 'cliente'])
            ->lockForUpdate()
            ->get();
    }

    private function validarInvariantes(
        Collection $certs,
        Obra $obra,
        string $numero,
    ): void {
        $totalCapitulos = Certificacion::where('obra_id', $obra->id)
            ->where('numero_certificacion', $numero)
            ->count();

        if ($certs->isEmpty() || $certs->count() !== $totalCapitulos) {
            throw new RuntimeException(
                'Existen capítulos no aceptados o ya facturados. No se puede facturar.'
            );
        }

        $clienteId = $certs->first()->cliente_id;
        if ($certs->contains(fn ($c) => $c->cliente_id !== $clienteId)) {
            throw new RuntimeException(
                'Las certificaciones no pertenecen al mismo cliente.'
            );
        }

        if ($certs->pluck('iva_porcentaje')->unique()->count() > 1) {
            throw new RuntimeException(
                'Las certificaciones no tienen el mismo IVA.'
            );
        }

        if ($certs->pluck('retencion_porcentaje')->unique()->count() > 1) {
            throw new RuntimeException(
                'Las certificaciones no tienen la misma retención.'
            );
        }
    }

    private function bloquearYAvanzarSerie(FacturaSerie $serie): FacturaSerie
    {
        $serie = FacturaSerie::where('id', $serie->id)
            ->lockForUpdate()
            ->firstOrFail();

        $serie->update(['ultimo_numero' => $serie->ultimo_numero + 1]);

        return $serie->fresh();
    }

    private function crearFactura(
        Obra $obra,
        string $numeroCertificacion,
        FacturaSerie $serie,
        Collection $certs,
    ): FacturaVenta {
        return FacturaVenta::create([
            'serie'                => $serie->serie,
            'numero_factura'       => $serie->ultimo_numero,
            'estado'               => 'emitida',
            'fecha_emision'        => now(),
            'fecha_contable'       => now(),
            'origen'               => 'certificacion',
            'codigo_certificacion' => $numeroCertificacion,
            'cliente_id'           => $certs->first()->cliente_id,
            'obra_id'              => $obra->id,
            'base_imponible'       => $certs->sum('base_imponible'),
            'iva_porcentaje'       => $certs->first()->iva_porcentaje,
            'iva_importe'          => $certs->sum('iva_importe'),
            'retencion_porcentaje' => $certs->first()->retencion_porcentaje,
            'retencion_importe'    => $certs->sum('retencion_importe'),
            'total'                => $certs->sum('total'),
        ]);
    }

    private function crearLineaFactura(FacturaVenta $factura, Certificacion $cert): void
    {
        FacturaVentaDetalle::create([
            'factura_venta_id' => $factura->id,
            'certificacion_id' => $cert->id,
            'concepto'         => 'Certificación ' . $cert->numero_certificacion
                . ' – ' . ($cert->oficio->nombre ?? 'Capítulo'),
            'cantidad'         => 1,
            'unidad'           => '1',
            'precio_unitario'  => $cert->base_imponible,
            'importe_linea'    => $cert->base_imponible,
        ]);
    }

    private function marcarCertificacionesComoFacturadas(Obra $obra, string $numero): void
    {
        Certificacion::where('obra_id', $obra->id)
            ->where('numero_certificacion', $numero)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->update(['estado_factura' => 'facturada']);
    }

    private function registrarEventoFacturada(Collection $certs, FacturaVenta $factura): void
    {
        $ahora = now();
        $userId = Auth::id();
        $referencia = $factura->serie . '-' . $factura->numero_factura;

        $rows = $certs->map(fn ($c) => [
            'certificacion_id' => $c->id,
            'user_id'          => $userId,
            'tipo'             => 'facturada',
            'estado_previo'    => 'pendiente',
            'estado_nuevo'     => 'facturada',
            'motivo'           => 'Factura ' . $referencia,
            'created_at'       => $ahora,
            'updated_at'       => $ahora,
        ])->all();

        CertificacionEvento::insert($rows);
    }
}
