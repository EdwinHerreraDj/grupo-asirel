<?php

namespace App\Services;

use App\Models\Certificacion;
use App\Models\CertificacionEvento;
use App\Models\FacturaSerie;
use App\Support\EstadoCobro;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaVentaEliminada;
use App\Models\FacturaVentaPago;
use App\Services\facturas\FacturaPdfService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
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

            // PDF ORIGINAL dentro de la transacci\u00f3n: si falla, rollback.
            $this->pdfService->generarOriginal($factura->fresh());

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

            // Si la factura nace de certificaciones, al anularla hay que
            // liberar esas certificaciones para que vuelvan a estar
            // disponibles (aceptadas / pendientes de factura). Si no,
            // quedan bloqueadas en 'facturada' para siempre.
            if ($factura->origen === 'certificacion') {
                $this->liberarCertificaciones($factura, $motivo);
            }
        });

        return $factura->fresh();
    }

    // -------------------------------------------------------------
    // ELIMINAR UNA FACTURA YA EMITIDA (excepcional)
    // -------------------------------------------------------------

    /**
     * ¿Es la última factura emitida de su serie? Solo esa se puede eliminar:
     * así el número vuelve al contador y la serie no queda con huecos.
     */
    public function esUltimaDeLaSerie(FacturaVenta $factura): bool
    {
        if (! $factura->serie || ! $factura->numero_factura) {
            return false;
        }

        return ! FacturaVenta::where('serie', $factura->serie)
            ->where('numero_factura', '>', $factura->numero_factura)
            ->exists();
    }

    /** Por qué no se puede eliminar, o null si sí se puede. */
    public function motivoNoEliminable(FacturaVenta $factura): ?string
    {
        if (! $factura->numero_factura) {
            return null; // Un borrador no ha consumido número.
        }

        if (! $this->esUltimaDeLaSerie($factura)) {
            return 'Solo se puede eliminar la última factura emitida de la serie, '
                .'para que la numeración no quede con huecos. Para esta factura, anúlala.';
        }

        return null;
    }

    /**
     * Elimina una factura y, si tenía número, lo devuelve a la serie.
     *
     * Se borra todo lo suyo (líneas, pagos, documentos, reimpresiones y los
     * PDF), las certificaciones vuelven a quedar pendientes y queda un
     * registro de quién la borró y por qué.
     */
    public function eliminar(FacturaVenta $factura, string $motivo): FacturaVentaEliminada
    {
        if ($impedimento = $this->motivoNoEliminable($factura)) {
            throw new RuntimeException($impedimento);
        }

        $factura->loadMissing(['cliente', 'obra', 'detalles', 'pagos', 'documentos']);

        return DB::transaction(function () use ($factura, $motivo) {
            // Con la serie bloqueada se vuelve a comprobar que sigue siendo la
            // última: otra persona puede haber emitido una mientras tanto.
            $serie = $factura->numero_factura
                ? FacturaSerie::where('serie', $factura->serie)->lockForUpdate()->first()
                : null;

            if ($factura->numero_factura && ! $this->esUltimaDeLaSerie($factura)) {
                throw new RuntimeException(
                    'Mientras tanto se ha emitido otra factura en la serie, así que esta ya no es la última.'
                );
            }

            $usuario = Auth::user();

            $registro = FacturaVentaEliminada::create([
                'factura_id' => $factura->id,
                'serie' => $factura->serie,
                'numero_factura' => $factura->numero_factura,
                'numero_formateado' => $factura->numeroFormateado(),
                'estado' => $factura->estado,
                'origen' => $factura->origen,
                'fecha_emision' => $factura->fecha_emision,
                'cliente_id' => $factura->cliente_id,
                'cliente_nombre' => $factura->cliente?->nombre,
                'obra_id' => $factura->obra_id,
                'obra_nombre' => $factura->obra?->nombre,
                'codigo_certificacion' => $factura->codigo_certificacion,
                'base_imponible' => $factura->base_imponible,
                'iva_importe' => $factura->iva_importe,
                'retencion_importe' => $factura->retencion_importe,
                'total' => $factura->total,
                'usuario_id' => $usuario?->id,
                'usuario_nombre' => $usuario?->name,
                'ip' => request()->ip(),
                'motivo' => $motivo,
                'numero_liberado' => $serie !== null && $factura->numero_factura !== null,
                'datos' => [
                    'factura' => $factura->toArray(),
                    'detalles' => $factura->detalles->toArray(),
                    'pagos' => $factura->pagos->toArray(),
                    'documentos' => $factura->documentos->toArray(),
                ],
            ]);

            // Las certificaciones vuelven a estar disponibles.
            if ($factura->origen === 'certificacion') {
                $this->liberarCertificaciones($factura, 'Factura eliminada: '.$motivo);
            }

            // Los adjuntos y sus archivos (las filas caen por clave foránea,
            // pero el archivo del disco hay que borrarlo a mano).
            foreach ($factura->documentos as $documento) {
                if ($documento->ruta && Storage::disk(self::DISCO)->exists($documento->ruta)) {
                    Storage::disk(self::DISCO)->delete($documento->ruta);
                }
            }

            $factura->delete(); // El modelo borra el PDF y el adjunto.

            // El número vuelve a quedar libre para la siguiente factura.
            if ($serie && (int) $serie->ultimo_numero === (int) $registro->numero_factura) {
                $serie->update(['ultimo_numero' => max(0, $registro->numero_factura - 1)]);
            }

            return $registro;
        });
    }

    /**
     * Revierte a 'pendiente' el estado_factura de las certificaciones ligadas
     * a esta factura y deja rastro en el historial de eventos.
     *
     * El enlace factura↔certificaciones es por `codigo_certificacion` =
     * `numero_certificacion` dentro de la misma obra (igual que hace
     * FacturaVentaGenerator al marcarlas como facturadas). La tabla pivote
     * `factura_venta_certificacion` no se rellena en este flujo.
     *
     * Solo libera si NINGUNA otra factura viva (no anulada) del mismo grupo
     * las mantiene facturadas: así una re-facturación activa no se desbloquea
     * por error.
     */
    private function liberarCertificaciones(FacturaVenta $factura, string $motivo): void
    {
        if (! $factura->codigo_certificacion || ! $factura->obra_id) {
            return;
        }

        $ligadaAOtraFacturaViva = FacturaVenta::where('id', '!=', $factura->id)
            ->where('obra_id', $factura->obra_id)
            ->where('codigo_certificacion', $factura->codigo_certificacion)
            ->where('origen', 'certificacion')
            ->where('estado', '!=', FacturaVenta::ESTADO_ANULADA)
            ->exists();

        if ($ligadaAOtraFacturaViva) {
            return;
        }

        $certs = Certificacion::where('obra_id', $factura->obra_id)
            ->where('numero_certificacion', $factura->codigo_certificacion)
            ->where('estado_factura', 'facturada')
            ->lockForUpdate()
            ->get();

        $ahora = now();
        $userId = Auth::id();
        $referencia = $factura->numeroFormateado();

        foreach ($certs as $cert) {
            $estadoCertPrevio = $cert->estado_certificacion;

            // Liberación completa: vuelve a 'pendiente' de factura Y de
            // certificación, de modo que quede directamente editable
            // (equivale a anular factura + anular certificación en un paso).
            $cert->update([
                'estado_factura'       => 'pendiente',
                'estado_certificacion' => 'pendiente',
            ]);

            CertificacionEvento::create([
                'certificacion_id' => $cert->id,
                'user_id'          => $userId,
                'tipo'             => 'factura_anulada',
                'estado_previo'    => $estadoCertPrevio,
                'estado_nuevo'     => 'pendiente',
                'motivo'           => 'Factura ' . $referencia . ' anulada: ' . $motivo,
                'created_at'       => $ahora,
                'updated_at'       => $ahora,
            ]);
        }
    }

    // -------------------------------------------------------------
    // ESTADO INFORMATIVO DE COBRO (no fiscal)
    // -------------------------------------------------------------

    /**
     * Cambia SOLO el estado informativo de cobro/seguimiento. No toca importes,
     * fechas, numeración, estado operativo, PDF ni trazabilidad fiscal.
     */
    public function cambiarEstadoCobro(FacturaVenta $factura, string $estadoCobro): FacturaVenta
    {
        if (! EstadoCobro::esValido($estadoCobro)) {
            throw new RuntimeException('Estado de cobro no válido.');
        }

        if (! $factura->puedeGestionarEstadoCobro()) {
            throw new RuntimeException('No se puede clasificar el cobro de esta factura en su estado actual.');
        }

        $factura->update([
            'estado_cobro'                 => $estadoCobro,
            'estado_cobro_actualizado_at'  => now(),
            'estado_cobro_actualizado_por' => Auth::id(),
        ]);

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
