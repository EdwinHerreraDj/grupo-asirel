<?php

namespace App\Exports;

use App\Models\Alquiler;
use App\Models\GastosVarios;
use App\Models\Material;
use App\Models\Obra;
use App\Models\ResumenFichajeMensual;
use App\Models\Subcontrata;
use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InformeGeneralExport implements FromArray, WithHeadings
{
    protected $obraId;

    public function __construct($obraId)
    {
        $this->obraId = $obraId;
    }

    public function headings(): array
    {
        return [
            'Concepto',
            'Importe (€)',
        ];
    }

    public function array(): array
    {
        $obra = Obra::findOrFail($this->obraId);

        $resumenes = ResumenFichajeMensual::where('obra_id', $obra->id)->get();
        $totalGanadoEmpleados = $resumenes->sum('total_ganado');

        $materiales = Material::where('obra_id', $obra->id)->get();
        $alquileres = Alquiler::where('obra_id', $obra->id)->get();
        $subcontratas = Subcontrata::where('obra_id', $obra->id)->get();
        $gastosVarios = GastosVarios::where('obra_id', $obra->id)->get();
        $ventas = Venta::where('obra_id', $obra->id)->get();

        $totalMateriales = $materiales->sum('importe');
        $totalAlquileres = $alquileres->sum('importe');
        $totalSubcontratas = $subcontratas->sum('importe');
        $totalGastosVarios = $gastosVarios->sum('importe');
        $totalGastos = $totalMateriales + $totalAlquileres + $totalSubcontratas + $totalGastosVarios;
        $totalVentas = $ventas->sum('importe');
        $balance = $totalVentas - $totalGastos;
        $rentable = $balance > 0 ? 'Rentable' : 'No Rentable';

        return [
            ['Obra', $obra->nombre],
            ['Total materiales', $totalMateriales],
            ['Total alquileres', $totalAlquileres],
            ['Total subcontratas', $totalSubcontratas],
            ['Total gastos varios', $totalGastosVarios],
            ['Total ganado empleados', $totalGanadoEmpleados],
            ['Total gastos', $totalGastos],
            ['Total ventas', $totalVentas],
            ['Balance', $balance],
            ['Estado', $rentable],
        ];
    }
}
