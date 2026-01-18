<?php

namespace App\Services;

use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\Certificacion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacturaVentaGenerator
{
    public function generarDesdeCodigoCertificacion(
        string $codigoCertificacion,
        array $data = []
    ): FacturaVenta {
        return DB::transaction(function () use ($codigoCertificacion, $data) {

            /**
             * Obtener certificaciones
             */
            $certificaciones = Certificacion::with('detalles')
                ->where('codigo_certificacion', $codigoCertificacion)
                ->get();

            if ($certificaciones->isEmpty()) {
                throw new \Exception('No hay certificaciones para este código.');
            }

            $obraId    = $certificaciones->first()->obra_id;
            $clienteId = $certificaciones->first()->cliente_id;

            /**
             * Numeración global
             */
            $serie = $data['serie'] ?? 'F';

            $ultimoNumero = FacturaVenta::where('serie', $serie)
                ->max('numero');

            $numero = ($ultimoNumero ?? 0) + 1;

            $numeroFactura = sprintf(
                '%s-%s-%06d',
                $serie,
                now()->year,
                $numero
            );

            /**
             * Calcular base total
             */
            $baseImponible = $certificaciones
                ->flatMap->detalles
                ->sum('importe_linea');

            $ivaPorcentaje       = $data['iva_porcentaje'] ?? 21;
            $retencionPorcentaje = $data['retencion_porcentaje'] ?? 0;

            $ivaImporte       = ($baseImponible * $ivaPorcentaje) / 100;
            $retencionImporte = ($baseImponible * $retencionPorcentaje) / 100;

            $total = $baseImponible + $ivaImporte - $retencionImporte;

            /**
             * Crear factura venta
             */
            $factura = FacturaVenta::create([
                'serie'                 => $serie,
                'numero'                => $numero,
                'numero_factura'        => $numeroFactura,
                'fecha_emision'         => $data['fecha_emision'] ?? Carbon::today(),
                'fecha_contable'        => $data['fecha_contable'] ?? null,
                'vencimiento'           => $data['vencimiento'] ?? null,
                'obra_id'               => $obraId,
                'cliente_id'            => $clienteId,
                'codigo_certificacion'  => $codigoCertificacion,
                'base_imponible'        => $baseImponible,
                'iva_porcentaje'        => $ivaPorcentaje,
                'iva_importe'           => $ivaImporte,
                'retencion_porcentaje'  => $retencionPorcentaje,
                'retencion_importe'     => $retencionImporte,
                'total'                 => $total,
                'estado'                => 'emitida',
            ]);

            /**
             * Copiar detalles
             */
            foreach ($certificaciones as $certificacion) {
                foreach ($certificacion->detalles as $detalle) {

                    FacturaVentaDetalle::create([
                        'factura_venta_id'      => $factura->id,
                        'certificacion_id'      => $certificacion->id,
                        'certificacion_detalle_id' => $detalle->id,
                        'concepto'              => $detalle->concepto,
                        'unidad'                => $detalle->unidad,
                        'cantidad'              => $detalle->cantidad,
                        'precio_unitario'       => $detalle->precio_unitario,
                        'importe_linea'         => $detalle->importe_linea,
                    ]);
                }
            }

            /**
             * Marcar certificaciones como facturadas (opcional)
             */
            Certificacion::whereIn('id', $certificaciones->pluck('id'))
                ->update(['facturada' => true]);

            return $factura;
        });
    }
}
