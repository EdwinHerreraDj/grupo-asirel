<?php

namespace App\Exports;

use App\Models\Alquiler;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlquileresObraExport implements FromCollection, WithHeadings, WithMapping
{
    protected $obraId;

    protected $nombre;

    protected $fechaInicio;

    protected $fechaFin;

    public function __construct($obraId, $nombre = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->obraId = $obraId;
        $this->nombre = $nombre;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $query = Alquiler::where('obra_id', $this->obraId);

        if ($this->nombre) {
            $query->where('nombre', 'LIKE', "%{$this->nombre}%");
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
            'Nombre',
            'Descripción',
            'Valor Unitario',
            'Cantidad',
            'Importe',
            'Número Factura',
            'Fecha',
        ];
    }

    public function map($alquiler): array
    {
        return [
            $alquiler->id,
            $alquiler->nombre,
            $alquiler->descripcion,
            number_format($alquiler->precio_unitario, 2).' €',
            $alquiler->cantidad,
            number_format($alquiler->importe, 2).' €',
            $alquiler->numero_factura,
            $alquiler->created_at->format('d/m/Y'),
        ];
    }
}
