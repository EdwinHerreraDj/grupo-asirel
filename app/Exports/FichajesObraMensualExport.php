<?php

namespace App\Exports;

use App\Models\ResumenFichajeMensual;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FichajesObraMensualExport implements FromCollection, WithHeadings, WithMapping
{
    protected $obraId;

    protected $mes;

    protected $anio;

    protected $resumen;

    public function __construct($obraId, $mes, $anio)
    {
        $this->obraId = $obraId;
        $this->mes = $mes;
        $this->anio = $anio;
    }

    public function collection(): Collection
    {
        $formatoMes = sprintf('%04d-%02d', $this->anio, $this->mes);

        $this->resumen = ResumenFichajeMensual::where('obra_id', $this->obraId)
            ->where('mes', $formatoMes)
            ->get();

        return $this->resumen;
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Mes',
            'Horas trabajadas',
            'Tarifa por hora',
            'Metros realizados',
            'Total ganado',
        ];
    }

    public function map($row): array
    {
        $empleadoNombre = optional($row->empleado)->nombre ?? 'Desconocido';

        return [
            $empleadoNombre,
            Carbon::createFromFormat('Y-m', $row->mes)->translatedFormat('F Y'),
            number_format($row->horas_trabajadas, 2),
            $row->tarifa_hora !== null ? number_format($row->tarifa_hora, 2).' €' : '—',
            $row->metros_realizados !== null ? $row->metros_realizados.' mt' : '—',
            $row->total_ganado !== null ? number_format($row->total_ganado, 2).' €' : '—',
        ];
    }
}
