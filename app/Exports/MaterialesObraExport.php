<?php

namespace App\Exports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialesObraExport implements FromCollection, WithHeadings, WithMapping
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
        // Crear la consulta base
        $query = Material::where('obra_id', $this->obraId);

        // Aplicar filtro por nombre si está presente
        if ($this->nombre) {
            $query->where('nombre', $this->nombre);
        }

        // Aplicar filtro por rango de fechas si ambos valores están presentes
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin]);
        }

        // Obtener los resultados
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripción',
            'Cantidad',
            'Precio Unitario',
            'Total',
            'Numero de Factura',
            'Fecha de Factura',
        ];
    }

    public function map($material): array
    {
        return [
            $material->nombre,
            $material->descripcion,
            $material->cantidad,
            $material->precio_unitario,
            $material->cantidad * $material->precio_unitario,
            $material->numero_factura,
            $material->fecha,
        ];
    }
}
