<?php

namespace App\Exports;

use App\Models\GastoGeneralEmpresa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class GastosEmpresaExport implements FromCollection, WithHeadings
{
    protected $inicio;
    protected $fin;

    public function __construct($inicio = null, $fin = null)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
    }

    public function collection()
    {
        return GastoGeneralEmpresa::with('categoria')
            ->when($this->inicio, fn($q) => $q->whereDate('fecha_gasto', '>=', $this->inicio))
            ->when($this->fin, fn($q) => $q->whereDate('fecha_gasto', '<=', $this->fin))
            ->orderByDesc('fecha_gasto')
            ->get()
            ->map(fn($gasto) => [
                'Concepto' => $gasto->concepto,
                'Categoría' => $gasto->categoria->nombre ?? '—',
                'Fecha' => $gasto->fecha_gasto
                    ? Carbon::parse($gasto->fecha_gasto)->format('d/m/Y')
                    : '',
                'Importe (€)' => number_format($gasto->importe, 2, ',', '.'),
                'Descripción' => $gasto->descripcion ?: '—',
            ]);
    }

    public function headings(): array
    {
        return ['Concepto', 'Categoría', 'Fecha', 'Importe (€)', 'Descripción'];
    }
}
