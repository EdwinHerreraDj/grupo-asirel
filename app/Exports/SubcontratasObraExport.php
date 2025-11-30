<?php

namespace App\Exports;

use App\Models\Subcontrata;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubcontratasObraExport implements FromCollection, WithHeadings, WithMapping
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
        $query = Subcontrata::where('obra_id', $this->obraId);

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
            'Importe',
            'Fecha',
        ];
    }

    public function map($subcontrata): array
    {
        return [
            $subcontrata->id,
            $subcontrata->nombre,
            $subcontrata->descripcion,
            number_format($subcontrata->importe, 2).' €',
            $subcontrata->fecha,
        ];
    }
}
