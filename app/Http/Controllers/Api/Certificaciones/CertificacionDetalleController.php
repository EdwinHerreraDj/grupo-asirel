<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Exceptions\CantidadExcedePendienteException;
use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\CertificacionDetalle;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;
use App\Services\CertificacionDetalleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CertificacionDetalleController extends Controller
{
    public function __construct(
        private readonly CertificacionDetalleService $service,
    ) {}

    public function show(Certificacion $certificacion): JsonResponse
    {
        $certificacion->load(['cliente', 'oficio', 'obra', 'detalles', 'eventos.user', 'facturasVenta']);

        return response()->json([
            'certificacion'  => $this->formatCertificacion($certificacion),
            'lineas'         => $certificacion->detalles->map(fn ($d) => $this->formatLinea($d)),
            'presupuesto'    => $this->getPresupuesto($certificacion),
            'partidas_venta' => $this->getPartidasVenta($certificacion),
            'eventos'        => $certificacion->eventos->map(fn ($e) => $this->formatEvento($e)),
        ]);
    }

    public function store(Request $request, Certificacion $certificacion): JsonResponse
    {
        $data = $request->validate([
            'presupuesto_venta_partida_id' => 'required|exists:presupuesto_venta_partidas,id',
            'cantidad'                     => 'required|numeric|gt:0',
            'forzar'                       => 'sometimes|boolean',
        ]);

        try {
            $this->service->crear($certificacion, $data, (bool) ($data['forzar'] ?? false));
        } catch (CantidadExcedePendienteException $e) {
            return response()->json([
                'message'           => $e->getMessage(),
                'exceso'            => true,
                'pendiente'         => $e->pendiente,
                'cantidad_intentada' => $e->cantidadIntentada,
            ], 409);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    public function update(
        Request $request,
        Certificacion $certificacion,
        CertificacionDetalle $detalle,
    ): JsonResponse {
        $data = $request->validate([
            'concepto'        => 'required|string|max:255',
            'unidad'          => 'required|string|max:100',
            'cantidad'        => 'required|numeric|gt:0',
            'precio_unitario' => 'required|numeric|min:0',
            'forzar'          => 'sometimes|boolean',
        ]);

        try {
            $this->service->actualizar(
                $certificacion,
                $detalle,
                $data,
                (bool) ($data['forzar'] ?? false),
            );
        } catch (CantidadExcedePendienteException $e) {
            return response()->json([
                'message'            => $e->getMessage(),
                'exceso'             => true,
                'pendiente'          => $e->pendiente,
                'cantidad_intentada' => $e->cantidadIntentada,
            ], 409);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    public function destroy(
        Certificacion $certificacion,
        CertificacionDetalle $detalle,
    ): JsonResponse {
        try {
            $this->service->eliminar($certificacion, $detalle);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    public function impuestos(Request $request, Certificacion $certificacion): JsonResponse
    {
        $data = $request->validate([
            'iva_porcentaje'       => 'required|numeric|min:0',
            'retencion_porcentaje' => 'required|numeric|min:0',
        ]);

        try {
            $this->service->aplicarImpuestos(
                $certificacion,
                (float) $data['iva_porcentaje'],
                (float) $data['retencion_porcentaje'],
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    public function aceptar(Certificacion $certificacion): JsonResponse
    {
        try {
            $this->service->aceptar($certificacion);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    public function anular(Request $request, Certificacion $certificacion): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->anular($certificacion, $data['motivo'] ?? null);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->respuestaCompleta($certificacion->fresh());
    }

    // -------------------------
    // HELPERS DE PRESENTACI\u00d3N
    // -------------------------

    private function respuestaCompleta(Certificacion $certificacion): JsonResponse
    {
        $certificacion->load(['cliente', 'oficio', 'obra', 'detalles', 'eventos.user', 'facturasVenta']);

        return response()->json([
            'certificacion'  => $this->formatCertificacion($certificacion),
            'lineas'         => $certificacion->detalles->map(fn ($d) => $this->formatLinea($d)),
            'presupuesto'    => $this->getPresupuesto($certificacion),
            'partidas_venta' => $this->getPartidasVenta($certificacion),
            'eventos'        => $certificacion->eventos->map(fn ($e) => $this->formatEvento($e)),
        ]);
    }

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
            'obra_nombre'          => $c->obra->nombre ?? '—',
            'puede_anular'         => $c->puedeAnular(),
            'avance_oficio'        => $this->calcularAvanceOficio($c),
            'factura_emitida'      => $this->getFacturaEmitida($c),
            'capitulos_hermanos'   => $this->getCapitulosHermanos($c),
        ];
    }

    /**
     * Si la certificaci\u00f3n est\u00e1 facturada, devuelve datos m\u00ednimos de la
     * factura asociada para que el frontend pueda enlazar.
     */
    private function getFacturaEmitida(Certificacion $c): ?array
    {
        if (! $c->estaFacturada()) {
            return null;
        }

        $factura = $c->facturasVenta->first();
        if (! $factura) {
            return null;
        }

        return [
            'id'         => $factura->id,
            'referencia' => $factura->serie . '-' . $factura->numero_factura,
            'url'        => route('empresa.facturas-ventas.detalle', $factura->id),
        ];
    }

    /**
     * Otras certificaciones del mismo numero_certificacion (capitulos hermanos),
     * para permitir navegaci\u00f3n directa entre ellas desde el detalle.
     */
    private function getCapitulosHermanos(Certificacion $c): array
    {
        if (! $c->numero_certificacion) {
            return [];
        }

        return Certificacion::with('oficio')
            ->where('obra_id', $c->obra_id)
            ->where('numero_certificacion', $c->numero_certificacion)
            ->orderBy('id')
            ->get()
            ->map(fn ($h) => [
                'id'            => $h->id,
                'oficio_nombre' => $h->oficio->nombre ?? '—',
                'actual'        => $h->id === $c->id,
                'url'           => route('empresa.certificaciones.show', $h->id),
            ])
            ->toArray();
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

    private function formatEvento($e): array
    {
        return [
            'id'            => $e->id,
            'tipo'          => $e->tipo,
            'estado_previo' => $e->estado_previo,
            'estado_nuevo'  => $e->estado_nuevo,
            'motivo'        => $e->motivo,
            'usuario'       => $e->user?->name,
            'fecha'         => $e->created_at,
        ];
    }

    private function getPresupuesto(Certificacion $certificacion): array
    {
        $capitulo = ObraPresupuestoVenta::where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
            ->first();

        if (! $capitulo) {
            return [
                'importe_contratado'   => 0,
                'cantidad_certificada' => 0,
                'importe_certificado'  => 0,
            ];
        }

        $importeContratado = $capitulo->partidas()->sum('importe');

        $scope = fn ($q) => $q->where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id);

        $importeCertificado = CertificacionDetalle::whereHas('certificacion', $scope)
            ->sum('importe_linea');

        $cantidadCertificada = CertificacionDetalle::whereHas('certificacion', $scope)
            ->sum('cantidad');

        return [
            'importe_contratado'   => round((float) $importeContratado, 2),
            'cantidad_certificada' => round((float) $cantidadCertificada, 4),
            'importe_certificado'  => round((float) $importeCertificado, 2),
        ];
    }

    /**
     * Avance de certificaci\u00f3n por oficio: cu\u00e1ntas partidas del oficio
     * ya tienen al menos una certificaci\u00f3n, sobre el total de partidas
     * del oficio. Para el indicador "3 de 7 partidas certificadas".
     */
    private function calcularAvanceOficio(Certificacion $c): array
    {
        $capitulo = ObraPresupuestoVenta::where('obra_id', $c->obra_id)
            ->where('obra_gasto_categoria_id', $c->obra_gasto_categoria_id)
            ->first();

        if (! $capitulo) {
            return ['certificadas' => 0, 'total' => 0];
        }

        $partidasIds = $capitulo->partidas()->pluck('id');
        $total = $partidasIds->count();

        $scope = fn ($q) => $q->where('obra_id', $c->obra_id)
            ->where('obra_gasto_categoria_id', $c->obra_gasto_categoria_id);

        $certificadas = CertificacionDetalle::whereHas('certificacion', $scope)
            ->whereIn('presupuesto_venta_partida_id', $partidasIds)
            ->distinct('presupuesto_venta_partida_id')
            ->count('presupuesto_venta_partida_id');

        return ['certificadas' => $certificadas, 'total' => $total];
    }

    private function getPartidasVenta(Certificacion $certificacion): array
    {
        $capitulo = ObraPresupuestoVenta::where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id)
            ->first();

        if (! $capitulo) {
            return [];
        }

        $scope = fn ($q) => $q->where('obra_id', $certificacion->obra_id)
            ->where('obra_gasto_categoria_id', $certificacion->obra_gasto_categoria_id);

        return $capitulo->partidas()
            ->get()
            ->map(function (PresupuestoVentaPartida $partida) use ($scope) {
                $certificadoAcumulado = CertificacionDetalle::whereHas('certificacion', $scope)
                    ->where('presupuesto_venta_partida_id', $partida->id)
                    ->sum('cantidad');

                return [
                    'id'                    => $partida->id,
                    'codigo'                => $partida->codigo,
                    'descripcion'           => $partida->descripcion,
                    'unidad'                => $partida->unidad,
                    'medicion'              => (float) $partida->medicion,
                    'precio_unitario'       => (float) $partida->precio_unitario,
                    'importe'               => (float) $partida->importe,
                    'certificado_acumulado' => (float) $certificadoAcumulado,
                    'pendiente'             => round($partida->medicion - $certificadoAcumulado, 4),
                ];
            })
            ->toArray();
    }
}
