<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\ObraPresupuestoVenta;
use App\Services\CertificacionCalculator;
use Illuminate\Http\Request;

class CertificacionDetalleController extends Controller
{
    // -------------------------
    // GET /api/certificaciones/{certificacion}
    // -------------------------
    public function show(Certificacion $certificacion)
    {
        $certificacion->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion' => $this->formatCertificacion($certificacion),
            'lineas'        => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'   => $this->getPresupuesto($certificacion),
            'partidas_venta' => $this->getPartidasVenta($certificacion),
        ]);
    }

    // -------------------------
    // POST /api/certificaciones/{certificacion}/lineas
    // -------------------------
    public function store(Request $request, Certificacion $certificacion)
    {
        if ($certificacion->estado_certificacion !== 'pendiente') {
            return response()->json(['message' => 'La certificación no admite modificaciones.'], 422);
        }

        $request->validate([
            'presupuesto_venta_partida_id' => 'required|exists:presupuesto_venta_partidas,id',
            'cantidad'                     => 'required|numeric|min:0',
            // concepto, unidad y precio_unitario vienen de la partida
        ]);

        $partida = \App\Models\PresupuestoVentaPartida::findOrFail(
            $request->presupuesto_venta_partida_id
        );

        $linea = CertificacionDetalle::create([
            'certificacion_id'             => $certificacion->id,
            'presupuesto_venta_partida_id' => $partida->id,
            'concepto'                     => $partida->descripcion,
            'unidad'                       => $partida->unidad,
            'cantidad'                     => $request->cantidad,
            'precio_unitario'              => $partida->precio_unitario,
            'importe_linea'                => $request->cantidad * $partida->precio_unitario,
        ]);

        app(CertificacionCalculator::class)->recalcular($certificacion->fresh());
        $certificacion->refresh()->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion'  => $this->formatCertificacion($certificacion),
            'lineas'         => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'    => $this->getPresupuesto($certificacion),
            'partidas_venta' => $this->getPartidasVenta($certificacion),
        ]);
    }

    // -------------------------
    // PUT /api/certificaciones/{certificacion}/lineas/{detalle}
    // -------------------------
    public function update(Request $request, Certificacion $certificacion, CertificacionDetalle $detalle)
    {
        if ($certificacion->estado_certificacion !== 'pendiente') {
            return response()->json(['message' => 'La certificación no admite modificaciones.'], 422);
        }

        $request->validate([
            'concepto'        => 'required|string|max:255',
            'unidad'          => 'required|string|max:100',
            'cantidad'        => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalle->update([
            'concepto'        => $request->concepto,
            'unidad'          => $request->unidad,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'importe_linea'   => $request->cantidad * $request->precio_unitario,
        ]);

        app(CertificacionCalculator::class)->recalcular($certificacion->fresh());

        $certificacion->refresh()->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion' => $this->formatCertificacion($certificacion),
            'lineas'        => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'   => $this->getPresupuesto($certificacion),
        ]);
    }

    // -------------------------
    // DELETE /api/certificaciones/{certificacion}/lineas/{detalle}
    // -------------------------
    public function destroy(Certificacion $certificacion, CertificacionDetalle $detalle)
    {
        if ($certificacion->estado_certificacion !== 'pendiente') {
            return response()->json(['message' => 'La certificación no admite modificaciones.'], 422);
        }

        $detalle->delete();

        app(CertificacionCalculator::class)->recalcular($certificacion->fresh());

        $certificacion->refresh()->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion' => $this->formatCertificacion($certificacion),
            'lineas'        => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'   => $this->getPresupuesto($certificacion),
        ]);
    }

    // -------------------------
    // PUT /api/certificaciones/{certificacion}/impuestos
    // -------------------------
    public function impuestos(Request $request, Certificacion $certificacion)
    {
        if ($certificacion->estado_certificacion !== 'pendiente') {
            return response()->json(['message' => 'La certificación no admite modificaciones.'], 422);
        }

        $request->validate([
            'iva_porcentaje'       => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
        ]);

        Certificacion::where('numero_certificacion', $certificacion->numero_certificacion)
            ->update([
                'iva_porcentaje'       => $request->iva_porcentaje,
                'retencion_porcentaje' => $request->retencion_porcentaje,
            ]);

        Certificacion::where('numero_certificacion', $certificacion->numero_certificacion)
            ->get()
            ->each(fn($c) => app(CertificacionCalculator::class)->recalcular($c));

        $certificacion->refresh()->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion'  => $this->formatCertificacion($certificacion),
            'lineas'         => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'    => $this->getPresupuesto($certificacion),
            'partidas_venta' => $this->getPartidasVenta($certificacion),
        ]);
    }

    // -------------------------
    // POST /api/certificaciones/{certificacion}/aceptar
    // -------------------------
    public function aceptar(Certificacion $certificacion)
    {
        if ($certificacion->estado_certificacion !== 'pendiente') {
            return response()->json(['message' => 'La certificación ya está aceptada.'], 422);
        }

        if ($certificacion->detalles->isEmpty()) {
            return response()->json(['message' => 'No puedes aceptar una certificación sin líneas.'], 422);
        }

        $certificacion->update(['estado_certificacion' => 'aceptada']);
        $certificacion->refresh()->load(['cliente', 'oficio', 'detalles']);

        return response()->json([
            'certificacion'  => $this->formatCertificacion($certificacion),
            'lineas'         => $certificacion->detalles->map(fn($d) => $this->formatLinea($d)),
            'presupuesto'    => $this->getPresupuesto($certificacion),   
            'partidas_venta' => $this->getPartidasVenta($certificacion),
        ]);
    }

    // -------------------------
    // HELPERS
    // -------------------------
    private function formatCertificacion(Certificacion $c): array
    {
        return [
            'id'                   => $c->id,
            'numero_certificacion' => $c->numero_certificacion,
            'oficio_id'            => $c->obra_gasto_categoria_id,
            'oficio_nombre'        => $c->oficio->nombre ?? '—',
            'cliente_id'           => $c->cliente_id,
            'cliente_nombre'       => $c->cliente->nombre ?? '—',
            'fecha_ingreso'        => $c->fecha_ingreso,
            'fecha_contable'       => $c->fecha_contable,
            'fecha_vencimiento'    => $c->fecha_vencimiento,
            'base_imponible'       => (float) $c->base_imponible,
            'iva_porcentaje'       => (float) $c->iva_porcentaje,
            'iva_importe'          => (float) $c->iva_importe,
            'retencion_porcentaje' => (float) $c->retencion_porcentaje,
            'retencion_importe'    => (float) $c->retencion_importe,
            'total'                => (float) $c->total,
            'estado_certificacion' => $c->estado_certificacion,
            'estado_factura'       => $c->estado_factura,
            'obra_id'              => $c->obra_id,
        ];
    }

    private function formatLinea(CertificacionDetalle $d): array
    {
        return [
            'id'                           => $d->id,
            'presupuesto_venta_partida_id' => $d->presupuesto_venta_partida_id,
            'concepto'                     => $d->concepto,
            'unidad'                       => $d->unidad,
            'cantidad'                     => (float) $d->cantidad,
            'precio_unitario'              => (float) $d->precio_unitario,
            'importe_linea'                => (float) $d->importe_linea,
        ];
    }

    private function getPresupuesto(Certificacion $certificacion): array
    {
        // Suma de importes de partidas de venta del capítulo
        $capitulo = \App\Models\ObraPresupuestoVenta::where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
            ->first();

        if (!$capitulo) {
            return [
                'cantidad_contratada'  => 0,
                'importe_contratado'   => 0,
                'cantidad_certificada' => 0,
                'importe_certificado'  => 0,
            ];
        }

        // Importe contratado = suma de partidas de venta del capítulo
        $importeContratado = $capitulo->partidas()->sum('importe');

        // Importe certificado acumulado = suma de importe_linea de detalles
        // vinculados a partidas de este capítulo
        $importeCertificado = \App\Models\CertificacionDetalle::whereHas(
            'certificacion',
            fn($q) => $q->where('obra_id', $certificacion->obra_id)
                ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
        )->sum('importe_linea');

        // Cantidad certificada acumulada
        $cantidadCertificada = \App\Models\CertificacionDetalle::whereHas(
            'certificacion',
            fn($q) => $q->where('obra_id', $certificacion->obra_id)
                ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
        )->sum('cantidad');

        return [
            'cantidad_contratada'  => 0, // ya no aplica como campo único
            'importe_contratado'   => round((float) $importeContratado, 2),
            'cantidad_certificada' => round((float) $cantidadCertificada, 4),
            'importe_certificado'  => round((float) $importeCertificado, 2),
        ];
    }

    private function getPartidasVenta(Certificacion $certificacion): array
    {
        // Capítulo de presupuesto de venta del mismo oficio
        $capitulo = \App\Models\ObraPresupuestoVenta::where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
            ->first();

        if (!$capitulo) return [];

        return $capitulo->partidas()
            ->get()
            ->map(function ($partida) use ($certificacion) {
                // Acumulado certificado de esta partida
                $certificadoAcumulado = \App\Models\CertificacionDetalle::whereHas(
                    'certificacion',
                    fn($q) => $q->where('obra_id', $certificacion->obra_id)
                        ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
                )
                    ->where('presupuesto_venta_partida_id', $partida->id)
                    ->sum('cantidad');

                return [
                    'id'                   => $partida->id,
                    'codigo'               => $partida->codigo,
                    'descripcion'          => $partida->descripcion,
                    'unidad'               => $partida->unidad,
                    'medicion'             => (float) $partida->medicion,
                    'precio_unitario'      => (float) $partida->precio_unitario,
                    'importe'              => (float) $partida->importe,
                    'certificado_acumulado' => (float) $certificadoAcumulado,
                    'pendiente'            => round($partida->medicion - $certificadoAcumulado, 4),
                ];
            })
            ->toArray();
    }

    private function comprobarExcede(Certificacion $certificacion, float $cantidad, float $precio): bool
    {
        $presupuesto = $this->getPresupuesto($certificacion);
        $importeCertificado = ($presupuesto['cantidad_certificada'] + $cantidad) * $precio;
        return $importeCertificado > $presupuesto['importe_contratado'];
    }
}
