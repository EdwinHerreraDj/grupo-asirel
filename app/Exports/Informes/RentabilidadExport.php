<?php

namespace App\Exports\Informes;

use App\Models\Obra;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class RentabilidadExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        public ?int $obraId = null,
        public ?string $estado = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null
    ) {}

    public function view(): View
    {
        $query = Obra::query()
            ->with(['materiales', 'alquileres', 'subcontratas', 'gastosVarios', 'ventas'])
            ->when($this->obraId, fn($q) => $q->where('id', $this->obraId))
            ->when($this->estado && $this->estado !== 'todas', fn($q) => $q->where('estado', $this->estado));

        $obras = $query->get();

        $resultado = $obras->map(function ($obra) {
            $filtroFechas = fn($q, $campo) => $q
                ->when($this->fechaInicio && $this->fechaFin, fn($x) => $x->whereBetween($campo, [$this->fechaInicio, $this->fechaFin]));

            // --- COSTES ---
            $materiales = $filtroFechas($obra->materiales(), 'fecha')->sum('importe');
            $alquileres = $filtroFechas($obra->alquileres(), 'fecha')->sum('importe');
            $subcontratas = $filtroFechas($obra->subcontratas(), 'fecha')->sum('importe');
            $gastosVarios = $filtroFechas($obra->gastosVarios(), 'fecha')->sum('importe');
            $costeTotal = $materiales + $alquileres + $subcontratas + $gastosVarios;

            // --- FACTURACIÓN ---
            $facturacionTotal = $filtroFechas($obra->ventas(), 'fecha')->sum('importe');

            // --- RENTABILIDAD ---
            $beneficio = $facturacionTotal - $costeTotal;
            $margen = $facturacionTotal > 0
                ? round(($beneficio / $facturacionTotal) * 100, 2)
                : 0;

            return [
                'nombre' => $obra->nombre,
                'estado' => ucfirst($obra->estado),
                'coste' => $costeTotal,
                'facturacion' => $facturacionTotal,
                'beneficio' => $beneficio,
                'margen' => $margen,
            ];
        });

        $totales = [
            'coste' => $resultado->sum('coste'),
            'facturacion' => $resultado->sum('facturacion'),
            'beneficio' => $resultado->sum('beneficio'),
        ];

        $totales['margen'] = $totales['facturacion'] > 0
            ? round(($totales['beneficio'] / $totales['facturacion']) * 100, 2)
            : 0;

        return view('exports.informes.rentabilidad', [
            'resultado' => $resultado,
            'totales' => $totales,
            'filtros' => [
                'estado' => $this->estado,
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
            ],
        ]);
    }

    public function title(): string
    {
        return 'Rentabilidad Coste-Venta';
    }
}
