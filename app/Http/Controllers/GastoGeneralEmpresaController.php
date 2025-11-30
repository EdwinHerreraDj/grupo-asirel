<?php

namespace App\Http\Controllers;

use App\Models\GastoGeneralEmpresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GastosEmpresaExport;
use Illuminate\Http\Request;

class GastoGeneralEmpresaController extends Controller
{
   /**
     * Exportar gastos en formato Excel
     */
    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('inicio');
        $fechaFin = $request->input('fin');

        return Excel::download(
            new GastosEmpresaExport($fechaInicio, $fechaFin),
            'informe_gastos_empresa.xlsx'
        );
    }

    /**
     * Exportar gastos en formato PDF
     */
    public function exportarPDF(Request $request)
    {
        // Aplicar filtros si se pasan fechas
        $query = GastoGeneralEmpresa::with('categoria');

        if ($request->filled('inicio') && $request->filled('fin')) {
            $query->whereBetween('fecha_gasto', [$request->inicio, $request->fin]);
        } elseif ($request->filled('inicio')) {
            $query->whereDate('fecha_gasto', '>=', $request->inicio);
        } elseif ($request->filled('fin')) {
            $query->whereDate('fecha_gasto', '<=', $request->fin);
        }

        $gastos = $query->orderByDesc('fecha_gasto')->get();

        if ($gastos->isEmpty()) {
            return back()->with('error', 'No se encontraron gastos en el rango seleccionado.');
        }

        // Crear PDF (mismo estilo que tu ejemplo de obras)
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('empresa.gastos_empresa.gastos_empresa_pdf', compact('gastos'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('informe_gastos_empresa.pdf');
    }
}
