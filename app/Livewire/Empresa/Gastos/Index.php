<?php

namespace App\Livewire\Empresa\Gastos;

use App\Models\CategoriaGastoEmpresa;
use App\Models\GastoGeneralEmpresa;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $gastoAEliminar = null;

    // Filtros aplicados
    public string $search = '';
    public string $filtroCategoria = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $importeMin = '';
    public string $importeMax = '';
    public string $estadoVencimiento = ''; // '', 'vencidos', 'proximos', 'sin_fecha'

    // Filtros temporales
    public string $pendingSearch = '';
    public string $pendingCategoria = '';
    public string $pendingFechaDesde = '';
    public string $pendingFechaHasta = '';
    public string $pendingImporteMin = '';
    public string $pendingImporteMax = '';
    public string $pendingEstadoVencimiento = '';

    protected $listeners = [
        'gastoGuardado' => 'cerrarModal',
        'cerrarModal'   => 'cerrarModal',
    ];

    public function aplicarFiltros(): void
    {
        if ($this->pendingFechaDesde && $this->pendingFechaHasta
            && $this->pendingFechaDesde > $this->pendingFechaHasta) {
            $this->dispatch('notify', type: 'error', message: 'La fecha desde no puede ser mayor que la fecha hasta.');

            return;
        }

        $this->search            = $this->pendingSearch;
        $this->filtroCategoria   = $this->pendingCategoria;
        $this->fechaDesde        = $this->pendingFechaDesde;
        $this->fechaHasta        = $this->pendingFechaHasta;
        $this->importeMin        = $this->pendingImporteMin;
        $this->importeMax        = $this->pendingImporteMax;
        $this->estadoVencimiento = $this->pendingEstadoVencimiento;

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search', 'filtroCategoria', 'fechaDesde', 'fechaHasta',
            'importeMin', 'importeMax', 'estadoVencimiento',
            'pendingSearch', 'pendingCategoria', 'pendingFechaDesde', 'pendingFechaHasta',
            'pendingImporteMin', 'pendingImporteMax', 'pendingEstadoVencimiento',
        ]);
        $this->resetPage();
    }

    public function abrirModal(): void
    {
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
    }

    public function confirmarEliminar($id): void
    {
        $this->gastoAEliminar = $id;
        $this->showDeleteModal = true;
    }

    public function cancelarEliminar(): void
    {
        $this->showDeleteModal = false;
        $this->gastoAEliminar = null;
    }

    public function eliminar(): void
    {
        $gasto = GastoGeneralEmpresa::find($this->gastoAEliminar);

        if ($gasto) {
            if ($gasto->factura_url && Storage::disk('public')->exists($gasto->factura_url)) {
                Storage::disk('public')->delete($gasto->factura_url);
            }
            $gasto->delete();
        }

        $this->showDeleteModal = false;
        $this->gastoAEliminar = null;

        $this->dispatch('notify', type: 'success', message: 'Gasto eliminado correctamente.');
    }

    private function aplicarFiltrosQuery($query)
    {
        if ($this->search) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('concepto', 'like', "%{$term}%")
                    ->orWhere('numero_factura', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%")
                    ->orWhere('especificacion', 'like', "%{$term}%");
            });
        }

        if ($this->filtroCategoria) {
            $query->where('categoria_id', $this->filtroCategoria);
        }

        if ($this->fechaDesde) {
            $query->whereDate('fecha_factura', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_factura', '<=', $this->fechaHasta);
        }

        if ($this->importeMin !== '') {
            $query->where('importe', '>=', (float) $this->importeMin);
        }

        if ($this->importeMax !== '') {
            $query->where('importe', '<=', (float) $this->importeMax);
        }

        if ($this->estadoVencimiento === 'vencidos') {
            $query->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', now()->toDateString());
        } elseif ($this->estadoVencimiento === 'proximos') {
            $query->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
                ->whereDate('fecha_vencimiento', '<=', now()->addDays(15)->toDateString());
        } elseif ($this->estadoVencimiento === 'sin_fecha') {
            $query->whereNull('fecha_vencimiento');
        }

        return $query;
    }

    public function render()
    {
        $filtrada = $this->aplicarFiltrosQuery(GastoGeneralEmpresa::query());

        $stats = [
            'total_count'    => (clone $filtrada)->count(),
            'total_importe'  => (clone $filtrada)->sum('importe'),
            'mes_actual'     => GastoGeneralEmpresa::whereYear('fecha_factura', now()->year)
                ->whereMonth('fecha_factura', now()->month)
                ->sum('importe'),
            'vencidos_count' => GastoGeneralEmpresa::whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', now()->toDateString())
                ->count(),
        ];

        return view('livewire.empresa.gastos.index', [
            'gastos' => $filtrada->with('categoria')
                ->orderBy('fecha_factura', 'desc')
                ->paginate(10),
            'categoriasPadre' => CategoriaGastoEmpresa::where('nivel', 1)
                ->with('children')
                ->ordenado()
                ->get(),
            'stats' => $stats,
        ]);
    }
}
