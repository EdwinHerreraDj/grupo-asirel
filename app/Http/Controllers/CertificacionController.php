<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\PDF;
use App\Models\Obra;
use App\Models\Certificacion;

class CertificacionController extends Controller
{
    public function index(Request $request, $id)
    {
        $obra = Obra::findOrFail($id);

        return view('obras.certificaciones.certificacion', [
            'obra' => $obra,
        ]);
    }

    public function facturar(Obra $obra)
    {
        return view('empresa.certificaciones.facturar', compact('obra'));
    }

    /* =====================================================
     * INFORME DE CERTIFICACIÓN (PDF bajo demanda)
     * ===================================================== */

    public function informe(Request $request)
    {
        $request->validate([
            'obra_id'              => 'required|integer',
            'numero_certificacion' => 'required|string',
            'capitulos'            => 'required|array|min:1',
            'capitulos.*'          => 'integer',
        ]);


        $obraId  = $request->obra_id;
        $numero  = $request->numero_certificacion;
        $ids     = $request->capitulos;

        // 🔒 Cargar capítulos seleccionados
        $certs = Certificacion::with(['oficio', 'detalles', 'cliente'])->whereIn('id', $ids)->get();

        // Validación: existen todos
        if ($certs->count() !== count($ids)) {
            abort(422, 'Uno o más capítulos no son válidos.');
        }

        // Validación: misma obra
        if ($certs->pluck('obra_id')->unique()->count() !== 1 || $certs->first()->obra_id !== $obraId) {
            abort(422, 'Los capítulos no pertenecen a la misma obra.');
        }

        // Validación: mismo número de certificación
        if ($certs->pluck('numero_certificacion')->unique()->count() !== 1) {
            abort(422, 'No se pueden mezclar certificaciones distintas.');
        }

        if ($certs->first()->numero_certificacion !== $numero) {
            abort(422, 'Número de certificación incorrecto.');
        }

        // Datos comunes
        $obra    = Obra::findOrFail($obraId);
        $cliente = $certs->first()->cliente;

        /* =========================
         * AGRUPACIÓN POR CAPÍTULO
         * ========================= */

        $capitulos = $certs->groupBy('oficio_id')->map(function ($grupo) {

            $detalles = $grupo->flatMap->detalles;

            $totalCapitulo = $detalles->sum('importe_linea');

            return [
                'oficio' => $grupo->first()->oficio->nombre ?? 'Capítulo',

                'lineas' => $detalles->map(function ($l) {
                    return [
                        'descripcion' => $l->concepto,
                        'cantidad'    => $l->cantidad,
                        'precio'      => $l->precio_unitario,
                        'total'       => $l->importe_linea,
                    ];
                }),

                'total' => $totalCapitulo,
            ];
        })->values();

        $totalGeneral = $capitulos->sum('total');

        /* =========================
         * DATA FINAL PARA PDF
         * ========================= */

        $data = [
            'obra' => $obra,
            'cliente' => $cliente,
            'numero_certificacion' => $numero,
            'fecha' => now()->format('d/m/Y'),
            'capitulos' => $capitulos,
            'total' => $totalGeneral,
        ];

        /* =========================
         * GENERAR PDF (NO SE GUARDA)
         * ========================= */

        $pdf = app('dompdf.wrapper');

        $pdf->loadView('pdf.informe-certificacion', $data)
            ->setPaper('A4')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream(
            'informe-certificacion-' . $numero . '.pdf'
        );
    }
}
