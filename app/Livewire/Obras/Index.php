<?php

namespace App\Livewire\Obras;

use App\Models\Certificacion;
use App\Models\FacturaRecibida;
use App\Models\Obra;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public string $estado = '';
    public ?int $obraAEliminarId = null;

    public function abrirEliminar(int $obraId): void
    {
        $this->obraAEliminarId = $obraId;
    }

    public function cancelarEliminar(): void
    {
        $this->obraAEliminarId = null;
    }

    #[On('obra-guardada')]
    public function refrescar(): void
    {
        // Livewire re-renderiza al llamar cualquier m\u00e9todo
    }

    public function eliminarObra(): void
    {
        $obra = Obra::findOrFail($this->obraAEliminarId);
        $obra->delete();

        $this->obraAEliminarId = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Obra eliminada correctamente.',
        ]);
    }

    public function render()
    {
        $query = Obra::with(['documentos']);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        $obras = $query->get()->map(function ($obra) {
            $total_gastos = FacturaRecibida::where('obra_id', $obra->id)
                ->where('estado', 'pagada')
                ->sum('importe');

            $total_ingresos = Certificacion::where('obra_id', $obra->id)
                ->where('tipo_documento', 'certificacion')
                ->sum('total');

            $total_documentos = $obra->documentos->count();
            $progreso_documentos = min(100, ($total_documentos / 9) * 100);

            $balance = $total_gastos > 0
                ? min(100, ($total_ingresos / $total_gastos) * 100)
                : ($total_ingresos > 0 ? 100 : 0);

            $obra->setAttribute('total_gastos', round($total_gastos, 2));
            $obra->setAttribute('total_ventas', round($total_ingresos, 2));
            $obra->setAttribute('balance', round($balance, 2));
            $obra->setAttribute('total_documentos', $total_documentos);
            $obra->setAttribute('progreso_documentos', round($progreso_documentos, 2));

            return $obra;
        });

        return view('livewire.obras.index', [
            'obras' => $obras,
        ]);
    }
}
