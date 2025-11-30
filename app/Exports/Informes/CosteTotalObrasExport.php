<?php

namespace App\Exports\Informes;

use App\Models\Obra;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CosteTotalObrasExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        public ?int $obraId = null,
        public ?string $estado = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null
    ) {}

    public function view(): View
    {
        // Carga las obras filtradas por estado u obra específica
        $query = Obra::query()
            ->with(['materiales', 'alquileres', 'subcontratas', 'gastosVarios'])
            ->when($this->obraId, fn($q) => $q->where('id', $this->obraId))
            ->when($this->estado && $this->estado !== 'todas', fn($q) => $q->where('estado', $this->estado));

        $obras = $query->get();

        // Mapeamos cada obra con sus costes
        $resultado = $obras->map(function ($obra) {
            $filtroFechas = fn($q, $campo) => $q
                ->when($this->fechaInicio && $this->fechaFin, fn($x) => $x->whereBetween($campo, [$this->fechaInicio, $this->fechaFin]));

            // --- Costes individuales ---
            $materiales = $filtroFechas($obra->materiales(), 'fecha')->sum('importe');
            $alquileres = $filtroFechas($obra->alquileres(), 'fecha')->sum('importe');
            $subcontratas = $filtroFechas($obra->subcontratas(), 'fecha')->sum('importe');
            $gastosVarios = $filtroFechas($obra->gastosVarios(), 'fecha')->sum('importe');

            // --- Total por obra ---
            return [
                'nombre' => $obra->nombre,
                'estado' => ucfirst($obra->estado),
                'materiales' => $materiales,
                'alquileres' => $alquileres,
                'subcontratas' => $subcontratas,
                'gastos_varios' => $gastosVarios,
                'total' => $materiales + $alquileres + $subcontratas + $gastosVarios,
            ];
        });

        // Total general de todas las obras filtradas
        $totalGlobal = $resultado->sum('total');

        return view('exports.informes.coste-total-obras', [
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
        return 'Coste Total de Obras';
    }
}
