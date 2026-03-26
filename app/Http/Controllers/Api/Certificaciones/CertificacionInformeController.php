<?php

namespace App\Http\Controllers\Api\Certificaciones;

use App\Http\Controllers\Controller;
use App\Models\Certificacion;
use App\Models\Obra;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificacionInformeController extends Controller
{
    // -------------------------
    // GET /api/obras/{obra}/certificaciones/{numero}/capitulos
    // Devuelve los capítulos de un numero_certificacion para el selector del informe
    // -------------------------
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

    // -------------------------
    // POST /api/certificaciones/informe-pdf
    // -------------------------
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

        $numero = $certs->first()->numero_certificacion;
        $total  = $certs->sum('total');

        $pdf = Pdf::loadView('pdf.certificacion', [
            'obra'                 => $obra,
            'empresa'              => $empresa,
            'cliente'              => $cliente,
            'fecha'                => now()->format('d/m/Y'),
            'numero_certificacion' => $numero,
            'capitulos'            => $capitulos,
            'total'                => $total,
        ])->setPaper('a4', 'portrait');

        $filename = 'informe_certificacion_' . str_replace(['/', '\\'], '-', $numero) . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename
        );
    }
}
