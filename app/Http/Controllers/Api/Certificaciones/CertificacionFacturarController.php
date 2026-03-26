<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\FacturaVenta;
use App\Models\FacturaVentaDetalle;
use App\Models\FacturaSerie;
use App\Models\Obra;
use App\Services\Facturas\FacturaPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CertificacionFacturarController extends Controller
{
    // -------------------------
    // GET /api/obras/{obra}/certificaciones/facturables
    // -------------------------
    public function facturables(Obra $obra)
    {
        $grupos = Certificacion::select(
            'numero_certificacion',
            'cliente_id',
            DB::raw('SUM(base_imponible) as base'),
            DB::raw('SUM(total) as total'),
            DB::raw('COUNT(*) as total_capitulos')
        )
            ->where('obra_id', $obra->id)
            ->where('estado_certificacion', 'aceptada')
            ->where('estado_factura', 'pendiente')
            ->groupBy('numero_certificacion', 'cliente_id')
            ->with('cliente')
            ->orderBy('numero_certificacion', 'desc')
            ->get()
            ->map(fn($g) => [
                'numero_certificacion' => $g->numero_certificacion,
                'cliente_nombre'       => $g->cliente->nombre ?? '—',
                'base'                 => (float) $g->base,
                'total'                => (float) $g->total,
                'total_capitulos'      => (int) $g->total_capitulos,
            ]);

        $series = FacturaSerie::where('activa', true)
            ->orderBy('serie')
            ->get(['id', 'serie']);

        return response()->json([
            'grupos'  => $grupos,
            'series'  => $series,
        ]);
    }

    // -------------------------
    // POST /api/obras/{obra}/certificaciones/facturar
    // -------------------------
    public function facturar(Request $request, Obra $obra)
    {
        $request->validate([
            'numero_certificacion' => 'required|string',
            'serie'                => 'required|string|exists:factura_series,serie',
        ]);

        try {
            $facturaId = DB::transaction(function () use ($request, $obra) {

                $totalCapitulos = Certificacion::where('obra_id', $obra->id)
                    ->where('numero_certificacion', $request->numero_certificacion)
                    ->lockForUpdate()
                    ->count();

                $certs = Certificacion::where('obra_id', $obra->id)
                    ->where('numero_certificacion', $request->numero_certificacion)
                    ->where('estado_certificacion', 'aceptada')
                    ->where('estado_factura', 'pendiente')
                    ->with(['oficio', 'cliente'])
                    ->lockForUpdate()
                    ->get();

                if ($certs->isEmpty() || $certs->count() !== $totalCapitulos) {
                    throw ValidationException::withMessages([
                        'certificacion' => 'Existen capítulos no aceptados. No se puede facturar.',
                    ]);
                }

                $clienteId = $certs->first()->cliente_id;
                if ($certs->contains(fn($c) => $c->cliente_id !== $clienteId)) {
                    throw ValidationException::withMessages([
                        'cliente' => 'Las certificaciones no pertenecen al mismo cliente.',
                    ]);
                }

                if ($certs->pluck('iva_porcentaje')->unique()->count() > 1) {
                    throw ValidationException::withMessages([
                        'iva' => 'Las certificaciones no tienen el mismo IVA.',
                    ]);
                }

                if ($certs->pluck('retencion_porcentaje')->unique()->count() > 1) {
                    throw ValidationException::withMessages([
                        'retencion' => 'Las certificaciones no tienen la misma retención.',
                    ]);
                }

                $serie = FacturaSerie::where('serie', $request->serie)
                    ->lockForUpdate()
                    ->firstOrFail();

                $numeroFactura = $serie->ultimo_numero + 1;
                $serie->update(['ultimo_numero' => $numeroFactura]);

                $factura = FacturaVenta::create([
                    'serie'                => $serie->serie,
                    'numero_factura'       => $numeroFactura,
                    'estado'               => 'emitida',
                    'fecha_emision'        => now(),
                    'fecha_contable'       => now(),
                    'origen'               => 'certificacion',
                    'codigo_certificacion' => $request->numero_certificacion,
                    'cliente_id'           => $clienteId,
                    'obra_id'              => $obra->id,
                    'base_imponible'       => $certs->sum('base_imponible'),
                    'iva_porcentaje'       => $certs->first()->iva_porcentaje,
                    'iva_importe'          => $certs->sum('iva_importe'),
                    'retencion_porcentaje' => $certs->first()->retencion_porcentaje,
                    'retencion_importe'    => $certs->sum('retencion_importe'),
                    'total'                => $certs->sum('total'),
                ]);

                foreach ($certs as $cert) {
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

                Certificacion::where('obra_id', $obra->id)
                    ->where('numero_certificacion', $request->numero_certificacion)
                    ->where('estado_certificacion', 'aceptada')
                    ->where('estado_factura', 'pendiente')
                    ->update(['estado_factura' => 'facturada']);

                return $factura->id;
            });

            $factura = FacturaVenta::findOrFail($facturaId);
            app(FacturaPdfService::class)->generar($factura);

            return response()->json([
                'factura_id'  => $facturaId,
                'redirect_url' => route('empresa.facturas-ventas.detalle', $facturaId),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Error al emitir la factura.',
            ], 500);
        }
    }
}
