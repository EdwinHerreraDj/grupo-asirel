<?php

namespace App\Exports\Informes;

use App\Models\Obra;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class FacturacionTotalExport implements FromView, WithTitle, ShouldAutoSize
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
            ->with('ventas')
            ->when($this->obraId, fn($q) => $q->where('id', $this->obraId))
            ->when($this->estado && $this->estado !== 'todas', fn($q) => $q->where('estado', $this->estado));

        $obras = $query->get();

        $resultado = $obras->map(function ($obra) {
            $filtroFechas = fn($q, $campo) => $q
                ->when($this->fechaInicio && $this->fechaFin, fn($x) => $x->whereBetween($campo, [$this->fechaInicio, $this->fechaFin]));

            $ventas = $filtroFechas($obra->ventas(), 'fecha')->get();

            $totalFacturado = $ventas->sum('importe');
            $primeraVenta = optional($ventas->sortBy('fecha')->first())->fecha;
            $ultimaVenta = optional($ventas->sortByDesc('fecha')->first())->fecha;

            $primeraVenta = $primeraVenta ? Carbon::parse($primeraVenta) : null;
            $ultimaVenta = $ultimaVenta ? Carbon::parse($ultimaVenta) : null;

            return [
                'nombre' => $obra->nombre,
                'estado' => ucfirst($obra->estado),
                'num_ventas' => $ventas->count(),
                'total' => $totalFacturado,
                'primera' => $primeraVenta ? $primeraVenta->format('d/m/Y') : '-',
                'ultima' => $ultimaVenta ? $ultimaVenta->format('d/m/Y') : '-',
            ];

        });

        $totalGlobal = $resultado->sum('total');

        return view('exports.informes.facturacion-total', [
            'resultado' => $resultado,
            'totalGlobal' => $totalGlobal,
            'filtros' => [
                'estado' => $this->estado,
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
            ],
        ]);
    }

    public function title(): string
    {
        return 'Facturación Total';
    }
}
