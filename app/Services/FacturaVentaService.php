<?php

namespace App\Services;

use App\Models\FacturaSerie;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaVentaPago;
use App\Services\Facturas\FacturaPdfService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Operaciones CRUD sobre facturas de venta MANUALES (borrador \u2192 emitida \u2192 cobrada/anulada).
 *
 * Para facturaci\u00f3n desde certificaciones, usa `FacturaVentaGenerator` \u2014
 * este servicio cubre el resto del ciclo.
 *
 * Garantiza:
 *   - mutaciones + subidas de archivo en la misma transacci\u00f3n (rollback seguro),
 *   - lock de la serie al consumir n\u00famero,
 *   - estado editable antes de tocar una factura,
 *   - recalculo fiscal y de estado de cobro centralizado,
 *   - PDF generado dentro de la transacci\u00f3n (coherencia con el Generator).
 */
class FacturaVentaService
{
    private const DISCO = 'public';
    private const CARPETA_ADJUNTOS = 'facturas/venta/adjuntos';

    public function __construct(
        private readonly FacturaPdfService $pdfService,
    ) {}

    // -------------------------------------------------------------
    // BORRADOR
    // -------------------------------------------------------------

    public function crearBorrador(array $data, ?UploadedFile $adjunto = null): FacturaVenta
    {
        return DB::transaction(function () use ($data, $adjunto) {
            $rutaAdjunto = $adjunto ? $this->guardarAdjunto($adjunto) : null;

            try {
                return FacturaVenta::create([
                    ...$data,
                    'origen'  => 'manual',
                    'estado'  => FacturaVenta::ESTADO_BORRADOR,
                    'adjunto' => $rutaAdjunto,
                ]);
            } catch (\Throwable $e) {
                if ($rutaAdjunto) {
                    $this->borrarAdjunto($rutaAdjunto);
                }
                throw $e;
            }
        });
    }

    public function actualizarBorrador(
        FacturaVenta $factura,
        array $data,
        ?UploadedFile $adjunto = null,
    ): FacturaVenta {
        if (! $factura->esEditable()) {
            throw new RuntimeException('Solo se pueden editar facturas en borrador.');
        }

        $adjuntoAnterior = $factura->adjunto;
        $rutaNueva = null;

        DB::transaction(function () use ($factura, $data, $adjunto, &$rutaNueva) {
            if ($adjunto) {
                $rutaNueva = $this->guardarAdjunto($adjunto);
                $data['adjunto'] = $rutaNueva;
            }

            try {
                $factura->update($data);
                $this->recalcularTotales($factura->fresh());
            } catch (\Throwable $e) {
                if ($rutaNueva) {
                    $this->borrarAdjunto($rutaNueva);
                }
                throw $e;
            }
        });

        if ($rutaNueva && $adjuntoAnterior && $adjuntoAnterior !== $rutaNueva) {
            $this->borrarAdjunto($adjuntoAnterior);
        }

        return $factura->fresh();
    }

    public function eliminarBorrador(FacturaVenta $factura): void
    {
        if (! $factura->esEditable()) {
            throw new RuntimeException('Solo se pueden eliminar facturas en borrador.');
        }

        DB::transaction(function () use ($factura) {
            // El hook deleting del modelo borra pdf_url y adjunto del disco.
            $factura->detalles()->delete();
            $factura->delete();
        });
    }

    // -------------------------------------------------------------
    // L\u00cdNEAS
    // -------------------------------------------------------------

    public function agregarLinea(FacturaVenta $factura, array $data): FacturaVentaDetalle
    {
        $this->asegurarEditable($factura);

        return DB::transaction(function () use ($factura, $data) {
            $cantidad = (float) $data['cantidad'];
            $precio = (float) $data['precio_unitario'];

            $linea = FacturaVentaDetalle::create([
                'factura_venta_id' => $factura->id,
                'concepto'         => $data['concepto'],
                'unidad'           => $data['unidad'] ?? null,
                'cantidad'         => $cantidad,
                'precio_unitario'  => $precio,
                'importe_linea'    => round($cantidad * $precio, 2),
            ]);

            $this->recalcularTotales($factura->fresh());

            return $linea;
        });
    }

    public function actualizarLinea(
        FacturaVenta $factura,
        FacturaVentaDetalle $linea,
        array $data,
    ): FacturaVentaDetalle {
        $this->asegurarEditable($factura);

        return DB::transaction(function () use ($factura, $linea, $data) {
            $cantidad = (float) $data['cantidad'];
            $precio = (float) $data['precio_unitario'];

            $linea->update([
                'concepto'        => $data['concepto'],
                'unidad'          => $data['unidad'] ?? null,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio,
                'importe_linea'   => round($cantidad * $precio, 2),
            ]);

            $this->recalcularTotales($factura->fresh());

            return $linea->fresh();
        });
    }

    public function eliminarLinea(FacturaVenta $factura, FacturaVentaDetalle $linea): void
    {
        $this->asegurarEditable($factura);

        DB::transaction(function () use ($factura, $linea) {
            $linea->delete();
            $this->recalcularTotales($factura->fresh());
        });
    }

    // -------------------------------------------------------------
    // EMISI\u00d3N
    // -------------------------------------------------------------

    public function emitir(FacturaVenta $factura): FacturaVenta
    {
        if (! $factura->puedeEmitirse()) {
            throw new RuntimeException('La factura no se puede emitir en su estado actual.');
        }

        return DB::transaction(function () use ($factura) {
            // Recalcular por si hubo edici\u00f3n sin refrescar
            $this->recalcularTotales($factura->fresh());

            // Bloquear y avanzar la serie
            $serie = FacturaSerie::where('serie', $factura->serie)
                ->lockForUpdate()
                ->firstOrFail();

            $serie->update(['ultimo_numero' => $serie->ultimo_numero + 1]);

            $factura->update([
                'numero_factura' => $serie->ultimo_numero,
                'estado'         => FacturaVenta::ESTADO_EMITIDA,
                'fecha_emision'  => $factura->fecha_emision ?? now(),
            ]);

            // PDF dentro de la transacci\u00f3n: si falla, rollback.
            $this->pdfService->generar($factura->fresh());

            return $factura->fresh();
        });
    }

    // -------------------------------------------------------------
    // PAGOS
    // -------------------------------------------------------------

    public function registrarPago(FacturaVenta $factura, array $data): FacturaVentaPago
    {
        if (! in_array($factura->estado, FacturaVenta::ESTADOS_COBRABLES, true)
            && $factura->estado !== FacturaVenta::ESTADO_PAGADA) {
            throw new RuntimeException('No se pueden registrar pagos en este estado.');
        }

        return DB::transaction(function () use ($factura, $data) {
            $pago = FacturaVentaPago::create([
                'factura_venta_id' => $factura->id,
                'fecha_pago'       => $data['fecha_pago'],
                'importe'          => (float) $data['importe'],
                'metodo'           => $data['metodo'],
                'tipo'             => $data['tipo'] ?? 'normal',
                'observaciones'    => $data['observaciones'] ?? null,
            ]);

            $this->recalcularEstadoFactura($factura->fresh());

            return $pago;
        });
    }

    public function actualizarPago(FacturaVentaPago $pago, array $data): FacturaVentaPago
    {
        return DB::transaction(function () use ($pago, $data) {
            $pago->update([
                'fecha_pago'    => $data['fecha_pago'],
                'importe'       => (float) $data['importe'],
                'metodo'        => $data['metodo'],
                'tipo'          => $data['tipo'] ?? $pago->tipo,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $this->recalcularEstadoFactura($pago->factura->fresh());

            return $pago->fresh();
        });
    }

    public function eliminarPago(FacturaVentaPago $pago): void
    {
        DB::transaction(function () use ($pago) {
            $factura = $pago->factura;
            $pago->delete();
            $this->recalcularEstadoFactura($factura->fresh());
        });
    }

    // -------------------------------------------------------------
    // ANULAR
    // -------------------------------------------------------------

    public function anular(FacturaVenta $factura, string $motivo): FacturaVenta
    {
        if (! $factura->puedeAnular()) {
            throw new RuntimeException('No se puede anular esta factura en su estado actual.');
        }

        DB::transaction(function () use ($factura, $motivo) {
            $factura->update([
                'estado'           => FacturaVenta::ESTADO_ANULADA,
                'motivo_anulacion' => $motivo,
            ]);
        });

        return $factura->fresh();
    }

    // -------------------------------------------------------------
    // INTERNOS
    // -------------------------------------------------------------

    private function asegurarEditable(FacturaVenta $factura): void
    {
        if (! $factura->esEditable()) {
            throw new RuntimeException('La factura no admite modificaciones en su estado actual.');
        }
    }

    /**
     * Suma importes de l\u00edneas y recalcula IVA / retenci\u00f3n / total.
     * No toca si la factura no es editable.
     */
    private function recalcularTotales(FacturaVenta $factura): void
    {
        if (! $factura->esEditable()) {
            return;
        }

        $base = (float) $factura->detalles()->sum('importe_linea');
        $ivaPct = (float) $factura->iva_porcentaje;
        $retPct = (float) $factura->retencion_porcentaje;

        $ivaImporte = round($base * $ivaPct / 100, 2);
        $retImporte = round($base * $retPct / 100, 2);
        $total = round($base + $ivaImporte - $retImporte, 2);

        $factura->update([
            'base_imponible'    => round($base, 2),
            'iva_importe'       => $ivaImporte,
            'retencion_importe' => $retImporte,
            'total'             => $total,
        ]);
    }

    /**
     * Tras altas/ediciones/bajas de pagos, ajusta el estado:
     *  - emitida/enviada con totalPagado >= total \u2192 pagada
     *  - pagada con totalPagado < total \u2192 vuelve a emitida
     */
    private function recalcularEstadoFactura(FacturaVenta $factura): void
    {
        $totalPagado = $factura->totalPagado();

        if (in_array($factura->estado, FacturaVenta::ESTADOS_COBRABLES, true)
            && $totalPagado >= $factura->total) {
            $factura->update(['estado' => FacturaVenta::ESTADO_PAGADA]);

            return;
        }

        if ($factura->estado === FacturaVenta::ESTADO_PAGADA && $totalPagado < $factura->total) {
            $factura->update(['estado' => FacturaVenta::ESTADO_EMITIDA]);
        }
    }

    private function guardarAdjunto(UploadedFile $archivo): string
    {
        $ruta = $archivo->store(self::CARPETA_ADJUNTOS, self::DISCO);

        if (! $ruta) {
            throw new RuntimeException('No se pudo guardar el adjunto.');
        }

        return $ruta;
    }

    private function borrarAdjunto(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }
}
