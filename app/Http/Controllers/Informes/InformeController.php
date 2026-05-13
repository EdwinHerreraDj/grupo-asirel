<?php

namespace App\Http\Controllers\Informes;

use App\Exports\Informes\AnalisisBrutoObrasExport;
use App\Exports\Informes\LiquidacionIvaExport;
use App\Exports\Informes\RetencionesObraExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InformeController extends Controller
{
    public function index()
    {
        return view('empresa.informes.index');
    }

    public function exportarLiquidacionIva(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio') ?: null;
        $fechaFin    = $request->get('fecha_fin') ?: null;
        $formato     = $request->get('formato', 'excel');

        $export = new LiquidacionIvaExport($fechaInicio, $fechaFin);

        if ($formato === 'excel') {
            return Excel::download($export, 'liquidacion_iva.xlsx');
        }

        if ($formato === 'pdf') {
            $view = $export->view();
            $pdf = Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'portrait');

            return $pdf->download('liquidacion_iva.pdf');
        }

        return back()->with('error', 'Formato no válido');
    }

    public function exportarRetencionesObra(Request $request)
    {
        $request->validate([
            'obra_id' => 'required|integer|exists:obras,id',
        ]);

        $obraId      = (int) $request->get('obra_id');
        $fechaInicio = $request->get('fecha_inicio') ?: null;
        $fechaFin    = $request->get('fecha_fin') ?: null;
        $formato     = $request->get('formato', 'pdf');

        $export = new RetencionesObraExport($obraId, $fechaInicio, $fechaFin);

        if ($formato === 'excel') {
            return Excel::download($export, 'retenciones_obra_' . $obraId . '.xlsx');
        }

        if ($formato === 'pdf') {
            $view = $export->view();
            $pdf = Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'portrait');

            return $pdf->download('retenciones_obra_' . $obraId . '.pdf');
        }

        return back()->with('error', 'Formato no válido');
    }

    public function exportarAnalisisBrutoObras(Request $request)
    {
        $obraId      = $request->get('obra_id') ?: null;
        $estado      = $request->get('estado') ?: null;
        $fechaInicio = $request->get('fecha_inicio') ?: null;
        $fechaFin    = $request->get('fecha_fin') ?: null;
        $formato     = $request->get('formato', 'excel');

        $export = new AnalisisBrutoObrasExport($obraId, $estado, $fechaInicio, $fechaFin);

        if ($formato === 'excel') {
            return Excel::download($export, 'analisis_bruto_obras.xlsx');
        }

        if ($formato === 'pdf') {
            $view = $export->view();
            $pdf = Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'landscape');

            return $pdf->download('analisis_bruto_obras.pdf');
        }

        return back()->with('error', 'Formato no válido');
    }
}
