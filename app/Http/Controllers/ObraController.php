<?php

namespace App\Http\Controllers;

use App\Exports\InformeGeneralExport;
use App\Models\Certificacion;
use App\Models\FacturaRecibida;
use App\Models\Obra;
use Maatwebsite\Excel\Facades\Excel;

class ObraController extends Controller
{
    public function informeGeneral($id)
    {
        $obra = Obra::findOrFail($id);

        $facturas = FacturaRecibida::where('obra_id', $id)
            ->where('estado', 'pagada')
            ->get();

        $totalGastos = $facturas->sum('importe');

        $certificaciones = Certificacion::where('obra_id', $id)
            ->where('tipo_documento', 'certificacion')
            ->get();

        $totalVentas = $certificaciones->sum('total');

        $resultado = $totalVentas - $totalGastos;

        if ($totalGastos > 0) {
            $balance = round(($totalVentas / $totalGastos) * 100, 2);
        } else {
            $balance = $totalVentas > 0 ? 100 : 0;
        }

        $rentable = $resultado >= 0 ? 'Rentable' : 'No rentable';

        $pdf = app('dompdf.wrapper');

        $pdf->loadView(
            'obras.pdf-general',
            compact(
                'obra',
                'facturas',
                'certificaciones',
                'totalGastos',
                'totalVentas',
                'resultado',
                'balance',
                'rentable'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->download('informe_general_obra_' . $id . '.pdf');
    }

    public function informeGeneralExcel($id)
    {
        $obra = Obra::findOrFail($id);
        $nombreArchivo = 'informe_general_obra_' . $obra->id . '.xlsx';

        return Excel::download(new InformeGeneralExport($obra->id), $nombreArchivo);
    }
}
