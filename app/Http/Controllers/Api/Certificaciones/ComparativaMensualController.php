<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Models\ObraPresupuestoVenta;
use App\Models\CertificacionDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ComparativaMensualController extends Controller
{
    // -------------------------
    // GET /api/obras/{obra}/comparativa-mensual?periodo=2026-03
    // -------------------------
    public function index(Request $request, Obra $obra)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');

        return response()->json([
            'filas'   => $this->calcularFilas($obra, $periodo),
            'periodo' => $periodo,
        ]);
    }

    // -------------------------
    // GET /api/obras/{obra}/comparativa-mensual/pdf?periodo=2026-03
    // -------------------------
    public function pdf(Request $request, Obra $obra)
    {
        $periodo = $request->periodo ?? now()->format('Y-m');
        $filas   = $this->calcularFilas($obra, $periodo);

        return response()->streamDownload(
            fn() => print(
                Pdf::loadView('pdf.comparativa-mensual', [
                    'obra'    => $obra,
                    'filas'   => $filas,
                    'periodo' => $periodo,
                ])->output()
            ),
            'comparativa-' . $obra->id . '-' . $periodo . '.pdf'
        );
    }

    // -------------------------
    // HELPER
    // -------------------------
    private function calcularFilas(Obra $obra, string $periodo): array
    {
        $inicio         = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $fin            = Carbon::createFromFormat('Y-m', $periodo)->endOfMonth();
        $finMesAnterior = $inicio->copy()->subDay();

        $oficios = $obra->categoriasGasto()
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED)")
            ->orderBy('nombre')
            ->get();

        // Presupuesto de venta por oficio — ahora desde partidas
        // Sumamos importe de partidas agrupado por capítulo
        $importeContratadoPorOficio = \App\Models\PresupuestoVentaPartida::whereHas(
            'capitulo',
            fn($q) => $q->where('obra_id', $obra->id)
        )
            ->with('capitulo')
            ->get()
            ->groupBy(fn($p) => $p->capitulo->obra_gasto_categoria_id)
            ->map(fn($partidas) => $partidas->sum('importe'));

        // Unidad del capítulo — tomamos la primera partida del capítulo
        $unidadPorOficio = \App\Models\PresupuestoVentaPartida::whereHas(
            'capitulo',
            fn($q) => $q->where('obra_id', $obra->id)
        )
            ->with('capitulo')
            ->get()
            ->groupBy(fn($p) => $p->capitulo->obra_gasto_categoria_id)
            ->map(fn($partidas) => $partidas->first()->unidad ?? '');

        // Medición del mes
        $medicionMes = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.cantidad) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $obra->id)
            ->whereBetween('certificaciones.fecha_ingreso', [$inicio, $fin])
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        // Origen hasta mes anterior
        $origenAnterior = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.cantidad) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $obra->id)
            ->where('certificaciones.fecha_ingreso', '<=', $finMesAnterior)
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        // Importe certificado del mes por oficio
        $importeMes = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.importe_linea) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $obra->id)
            ->whereBetween('certificaciones.fecha_ingreso', [$inicio, $fin])
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        // Importe certificado hasta mes anterior
        $importeOrigenAnterior = CertificacionDetalle::selectRaw(
            'certificaciones.obra_gasto_categoria_id, SUM(certificacion_detalles.importe_linea) as total'
        )
            ->join('certificaciones', 'certificacion_detalles.certificacion_id', '=', 'certificaciones.id')
            ->where('certificaciones.obra_id', $obra->id)
            ->where('certificaciones.fecha_ingreso', '<=', $finMesAnterior)
            ->groupBy('certificaciones.obra_gasto_categoria_id')
            ->pluck('total', 'obra_gasto_categoria_id');

        $filas = [];

        foreach ($oficios as $oficio) {
            $importeContratado = (float) ($importeContratadoPorOficio[$oficio->id] ?? 0);
            $unidad            = $unidadPorOficio[$oficio->id] ?? '';

            $cantMes        = (float) ($medicionMes[$oficio->id] ?? 0);
            $cantOrigenAnt  = (float) ($origenAnterior[$oficio->id] ?? 0);
            $cantAOrigen    = $cantOrigenAnt + $cantMes;

            $impMes        = (float) ($importeMes[$oficio->id] ?? 0);
            $impOrigenAnt  = (float) ($importeOrigenAnterior[$oficio->id] ?? 0);
            $impAOrigen    = $impOrigenAnt + $impMes;

            $filas[] = [
                'oficio'          => $oficio->nombre,
                'unidad'          => $unidad,
                'contrato'        => $importeContratado,
                'origen_anterior' => $cantOrigenAnt,
                'mes'             => $cantMes,
                'a_origen'        => $cantAOrigen,
                'pendiente'       => $importeContratado - $impAOrigen,
                'importe_mes'     => $impMes,
                'importe_origen'  => $impAOrigen,
            ];
        }

        return $filas;
    }
}
