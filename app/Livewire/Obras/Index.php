<?php

namespace App\Livewire\Obras;

use App\Models\FacturaRecibida;
use App\Models\FacturaVenta;
use App\Models\Obra;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    // Estados que cuentan fiscalmente como venta real
    private const VENTA_ESTADOS_FISCALES = ['emitida', 'enviada', 'pagada'];

    // Estados de factura recibida que NO cuentan como gasto
    private const RECIBIDA_ESTADOS_EXCLUIDOS = ['devuelta'];

    public string $estado = '';
    public string $search = '';
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

        if ($search = trim($this->search)) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        $obras = $query->orderBy('nombre')->get();
        $obraIds = $obras->pluck('id');

        // Sumas de venta y gasto por obra en consultas únicas (evita N+1)
        $ventasPorObra = FacturaVenta::whereIn('obra_id', $obraIds)
            ->whereIn('estado', self::VENTA_ESTADOS_FISCALES)
            ->select('obra_id', DB::raw('SUM(total) as total_ventas'))
            ->groupBy('obra_id')
            ->pluck('total_ventas', 'obra_id');

        $gastosPorObra = FacturaRecibida::whereIn('obra_id', $obraIds)
            ->whereNotIn('estado', self::RECIBIDA_ESTADOS_EXCLUIDOS)
            ->select('obra_id', DB::raw('SUM(total) as total_gastos'))
            ->groupBy('obra_id')
            ->pluck('total_gastos', 'obra_id');

        $obras = $obras->map(function ($obra) use ($ventasPorObra, $gastosPorObra) {
            $total_ventas = (float) ($ventasPorObra[$obra->id] ?? 0);
            $total_gastos = (float) ($gastosPorObra[$obra->id] ?? 0);

            $total_documentos = $obra->documentos->count();
            $progreso_documentos = min(100, ($total_documentos / 9) * 100);

            // Balance = qué % del gasto está cubierto por venta facturada
            $balance = $total_gastos > 0
                ? min(100, ($total_ventas / $total_gastos) * 100)
                : ($total_ventas > 0 ? 100 : 0);

            $obra->setAttribute('total_gastos', round($total_gastos, 2));
            $obra->setAttribute('total_ventas', round($total_ventas, 2));
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
