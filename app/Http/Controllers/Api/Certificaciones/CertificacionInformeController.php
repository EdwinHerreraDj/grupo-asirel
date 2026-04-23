<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\Empresa;
use App\Models\Obra;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificacionInformeController extends Controller
{
    // GET /api/obras/{obra}/certificaciones/{numero}/capitulos
    public function capitulos(Obra $obra, string $numero)
    {
        $capitulos = Certificacion::with('oficio')
            ->where('obra_id', $obra->id)
            ->where('numero_certificacion', $numero)
            ->get()
            ->map(fn($c) => [
                'id'     => $c->id,
                'oficio' => $c->oficio->nombre ?? 'Capítulo',
                'total'  => (float) $c->total,
            ]);

        return response()->json(['capitulos' => $capitulos]);
    }

    // POST /api/certificaciones/informe-pdf
    public function pdf(Request $request)
    {
        $request->validate([
            'certificacion_ids'   => 'required|array|min:1',
            'certificacion_ids.*' => 'exists:certificaciones,id',
        ]);

        $certs = Certificacion::with(['oficio', 'detalles', 'cliente'])
            ->whereIn('id', $request->certificacion_ids)
            ->get();

        if ($certs->isEmpty()) {
            return response()->json(['message' => 'No hay datos.'], 422);
        }

        if ($certs->pluck('obra_id')->unique()->count() !== 1) {
            return response()->json(['message' => 'Los capítulos no pertenecen a la misma obra.'], 422);
        }

        $obra    = Obra::findOrFail($certs->first()->obra_id);
        $empresa = Empresa::first();
        $cliente = $certs->first()->cliente;

        $capitulos = $certs->groupBy('obra_gasto_categoria_id')
            ->map(function ($grupo) {
                $lineas = $grupo->flatMap->detalles->map(fn($d) => [
                    'descripcion' => $d->concepto,
                    'unidad'      => $d->unidad,
                    'cantidad'    => (float) $d->cantidad,
                    'precio'      => (float) $d->precio_unitario,
                    'total'       => (float) $d->importe_linea,
                ])->values()->toArray();

                return [
                    'oficio' => $grupo->first()->oficio->nombre ?? 'Capítulo',
                    'lineas' => $lineas,
                    'total'  => collect($lineas)->sum('total'),
                ];
            })->values()->toArray();

        // ===== DESGLOSE FISCAL =====
        $baseImponible  = (float) $certs->sum('base_imponible');
        $ivaImporte     = (float) $certs->sum('iva_importe');
        $retencionImp   = (float) $certs->sum('retencion_importe');
        $totalFinal     = (float) $certs->sum('total');

        // Agrupar IVA por porcentaje (puede haber certificaciones con tipos distintos)
        $ivaPorTipo = $certs
            ->groupBy(fn($c) => (string) (float) $c->iva_porcentaje)
            ->map(fn($grupo) => [
                'porcentaje' => (float) $grupo->first()->iva_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'importe'    => (float) $grupo->sum('iva_importe'),
            ])
            ->sortBy('porcentaje')
            ->values()
            ->toArray();

        // Agrupar Retención por porcentaje (solo si > 0)
        $retencionPorTipo = $certs
            ->filter(fn($c) => (float) $c->retencion_porcentaje > 0)
            ->groupBy(fn($c) => (string) (float) $c->retencion_porcentaje)
            ->map(fn($grupo) => [
                'porcentaje' => (float) $grupo->first()->retencion_porcentaje,
                'base'       => (float) $grupo->sum('base_imponible'),
                'importe'    => (float) $grupo->sum('retencion_importe'),
            ])
            ->sortBy('porcentaje')
            ->values()
            ->toArray();

        $totales = [
            'base'           => $baseImponible,
            'iva_total'      => $ivaImporte,
            'retencion_total' => $retencionImp,
            'total'          => $totalFinal,
            'iva_grupos'     => $ivaPorTipo,
            'retencion_grupos' => $retencionPorTipo,
        ];

        $numero = $certs->first()->numero_certificacion;

        $pdf = Pdf::loadView('pdf.certificacion', [
            'obra'                 => $obra,
            'empresa'              => $empresa,
            'cliente'              => $cliente,
            'fecha'                => now()->format('d/m/Y'),
            'numero_certificacion' => $numero,
            'capitulos'            => $capitulos,
            'totales'              => $totales,
        ])->setPaper('a4', 'portrait');

        $filename = 'informe_certificacion_' . str_replace(['/', '\\'], '-', $numero) . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename
        );
    }
}
