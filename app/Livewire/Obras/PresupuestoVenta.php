<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use App\Models\Obra;
use App\Models\ObraGastoCategoria;
use App\Models\ObraPresupuestoVenta;
use Barryvdh\DomPDF\Facade\Pdf;

class PresupuestoVenta extends Component
{
    public Obra $obra;

    /**
     * Array indexado por obra_gasto_categoria_id
     */
    public array $presupuestos = [];

    public function mount(Obra $obra)
    {
        $this->obra = $obra;

        // Cargar oficios de la obra
        $oficios = $this->obra->categoriasGasto()
            ->orderByRaw("
                CAST(
                    SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED
                ) ASC
            ")
            ->orderBy('nombre')
            ->get();

        foreach ($oficios as $oficio) {

            $presupuesto = ObraPresupuestoVenta::where('obra_id', $obra->id)
                ->where('obra_gasto_categoria_id', $oficio->id)
                ->first();

            $this->presupuestos[$oficio->id] = [
                'id'              => $presupuesto?->id,
                'unidad'          => $presupuesto?->unidad,
                'cantidad'        => $presupuesto?->cantidad,
                'precio_unitario' => $presupuesto?->precio_unitario,
                'importe_total'   => $presupuesto?->importe_total,
                'observaciones'   => $presupuesto?->observaciones,
            ];
        }
    }

    public function guardar()
    {
        foreach ($this->presupuestos as $oficioId => $data) {

            // No guardar filas completamente vacías
            if (
                is_null($data['cantidad']) &&
                is_null($data['precio_unitario'])
            ) {
                continue;
            }

            ObraPresupuestoVenta::updateOrCreate(
                [
                    'obra_id' => $this->obra->id,
                    'obra_gasto_categoria_id' => $oficioId,
                ],
                [
                    'unidad'          => $data['unidad'],
                    'cantidad'        => $data['cantidad'],
                    'precio_unitario' => $data['precio_unitario'],
                    'observaciones'   => $data['observaciones'],
                ]
            );
        }

        $this->dispatch('toast', type: 'success', text: 'Presupuesto de venta guardado correctamente.');
    }

    public function descargarInforme()
    {
        // Oficios (ordenados)
        $oficios = $this->obra->categoriasGasto()
            ->orderByRaw("
            CAST(
                SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED
            ) ASC
        ")
            ->orderBy('nombre')
            ->get();

        // Presupuesto de venta
        $presupuestos = ObraPresupuestoVenta::where('obra_id', $this->obra->id)
            ->get()
            ->keyBy('obra_gasto_categoria_id');

        // Total
        $total = $presupuestos->sum('importe_total');

        $pdf = Pdf::loadView(
            'pdf.presupuesto-venta',
            [
                'obra'         => $this->obra,
                'oficios'      => $oficios,
                'presupuestos' => $presupuestos,
                'total'        => $total,
            ]
        )->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Presupuesto_venta_' . $this->obra->id . '.pdf'
        );
    }

    public function render()
    {
        // ============================
        // OFICIOS (mismo orden que mount)
        // ============================
        $oficios = $this->obra->categoriasGasto()
            ->orderByRaw("
            CAST(
                SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED
            ) ASC
        ")
            ->orderBy('nombre')
            ->get();

        // ============================
        // COSTES TEÓRICOS
        // tabla: obra_gastos_iniciales
        // ============================
        $costesTeoricos = $this->obra->gastosIniciales
            ->pluck('pivot.importe', 'id')
            ->toArray();

        return view('livewire.obras.presupuesto-venta', [
            'oficios'        => $oficios,
            'costesTeoricos' => $costesTeoricos,
        ]);
    }
}
