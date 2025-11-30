<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\Informes\CosteTotalObrasExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Informes\FacturacionTotalExport;
use App\Exports\Informes\RentabilidadExport;
use App\Exports\Informes\CosteVentaMensualExport;


class InformeController extends Controller
{
    public function Index()
    {
        return view('empresa.informes.index');
    }

    public function exportarCosteTotalObras(Request $request)
    {
        $obraId = $request->get('obra_id');
        $estado = $request->get('estado');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $formato = $request->get('formato', 'excel');

        if ($formato === 'excel') {
            return Excel::download(
                new CosteTotalObrasExport($obraId, $estado, $fechaInicio, $fechaFin),
                'coste_total_obras.xlsx'
            );
        }

        if ($formato === 'pdf') {
            $export = new CosteTotalObrasExport($obraId, $estado, $fechaInicio, $fechaFin);
            $view = $export->view();

            $pdf = Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'landscape');

            return $pdf->download('coste_total_obras.pdf');
        }

        return back()->with('error', 'Formato no válido');
    }

    public function exportarFacturacionTotal(Request $request)
    {
        $obraId = $request->get('obra_id');
        $estado = $request->get('estado');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $formato = $request->get('formato', 'excel');   

        if ($formato === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new FacturacionTotalExport($obraId, $estado, $fechaInicio, $fechaFin),
                'facturacion_total.xlsx'
            );
        }   

        if ($formato === 'pdf') {
            $export = new FacturacionTotalExport($obraId, $estado, $fechaInicio, $fechaFin);
            $view = $export->view();    

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'landscape');  

            return $pdf->download('facturacion_total.pdf');
        }   

        return back()->with('error', 'Formato no válido');
    }


    public function exportarRentabilidad(Request $request)
    {
        $obraId = $request->get('obra_id');
        $estado = $request->get('estado');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $formato = $request->get('formato', 'excel');
    
        if ($formato === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new RentabilidadExport($obraId, $estado, $fechaInicio, $fechaFin),
                'rentabilidad_obras.xlsx'
            );
        }
    
        if ($formato === 'pdf') {
            $export = new RentabilidadExport($obraId, $estado, $fechaInicio, $fechaFin);
            $view = $export->view();
        
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'landscape');
        
            return $pdf->download('rentabilidad_obras.pdf');
        }
    
        return back()->with('error', 'Formato no válido');
    }


    public function exportarCosteVentaMensual(Request $request)
    {
        $obraId = $request->get('obra_id');
        $estado = $request->get('estado');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $porcentaje = floatval($request->get('porcentaje', 10));
        $formato = $request->get('formato', 'excel');
    
        if ($formato === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new CosteVentaMensualExport($obraId, $estado, $fechaInicio, $fechaFin, $porcentaje),
                'coste-venta-mensual.xlsx'
            );
        }
    
        if ($formato === 'pdf') {
            $export = new CosteVentaMensualExport($obraId, $estado, $fechaInicio, $fechaFin, $porcentaje);
            $view = $export->view();
        
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view->name(), $view->getData())
                ->setPaper('a4', 'landscape');
        
            return $pdf->download('coste-venta-mensual.pdf');
        }
    
        return back()->with('error', 'Formato no válido');
    }
}
