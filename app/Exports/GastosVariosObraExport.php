<?php

namespace App\Exports;

use App\Models\GastosVarios;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GastosVariosObraExport implements FromCollection, WithHeadings, WithMapping
{
    protected $obraId;

    protected $tipo;

    protected $fechaInicio;

    protected $fechaFin;

    public function __construct($obraId, $tipo = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->obraId = $obraId;
        $this->tipo = $tipo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $query = GastosVarios::where('obra_id', $this->obraId);

        if ($this->tipo) {
            $query->where('tipo', 'LIKE', "%{$this->tipo}%");
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Descripción',
            'Importe',
            'Fecha',
            'Número Factura',
        ];
    }

    public function map($gasto): array
    {
        return [
            $gasto->id,
            $gasto->tipo,
            $gasto->descripcion,
            number_format($gasto->importe, 2, ',', '.').' €',
            $gasto->fecha ? $gasto->fecha->format('d/m/Y') : 'Sin fecha',
            $gasto->numero_factura,
        ];
    }
}
