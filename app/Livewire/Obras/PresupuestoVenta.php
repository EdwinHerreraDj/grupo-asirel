<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use App\Models\Obra;
use App\Models\ObraPresupuestoVenta;
use Barryvdh\DomPDF\Facade\Pdf;

class PresupuestoVenta extends Component
{
    public Obra $obra;
    public ?int $capituloActivoId = null; 
    public function mount(Obra $obra): void
    {
        $this->obra = $obra;
    }

    protected $listeners = ['partida-actualizada' => '$refresh'];

    public function toggleCapitulo(int $oficioId): void
    {
        if ($this->capituloActivoId === $oficioId) {
            $this->capituloActivoId = null;
            return;
        }

        // Crear registro en presupuesto venta si no existe todavía
        ObraPresupuestoVenta::firstOrCreate(
            [
                'obra_id'                 => $this->obra->id,
                'obra_gasto_categoria_id' => $oficioId,
            ],
            [
                'importe_total' => 0,
            ]
        );

        $this->capituloActivoId = $oficioId;
    }

    public function descargarInforme()
    {
        $oficios = $this->obra->categoriasGasto()
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get();

        $presupuestos = ObraPresupuestoVenta::where('obra_id', $this->obra->id)
            ->with('partidas')
            ->get()
            ->keyBy('obra_gasto_categoria_id');

        $total = $presupuestos->sum('importe_total');

        $pdf = Pdf::loadView('pdf.presupuesto-venta', [
            'obra'         => $this->obra,
            'oficios'      => $oficios,
            'presupuestos' => $presupuestos,
            'total'        => $total,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Presupuesto_venta_' . $this->obra->id . '.pdf'
        );
    }

    public function render()
    {
        $oficios = $this->obra->categoriasGasto()
            ->orderByRaw("CAST(SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED) ASC")
            ->orderBy('nombre')
            ->get();

        $capitulos = ObraPresupuestoVenta::where('obra_id', $this->obra->id)
            ->with(['partidas', 'oficio'])
            ->get()
            ->keyBy('obra_gasto_categoria_id'); 

        $costesTeoricos = $this->obra->gastosIniciales
            ->pluck('pivot.importe', 'id')
            ->toArray();

        return view('livewire.obras.presupuesto-venta', [
            'oficios'        => $oficios,
            'capitulos'      => $capitulos,
            'costesTeoricos' => $costesTeoricos,
        ]);
    }
}
