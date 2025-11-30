<?php

namespace App\Livewire\Empresa\Gastos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GastoGeneralEmpresa;
use Illuminate\Support\Facades\Storage;
use App\Models\CategoriaGastoEmpresa;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $showDeleteModal = false;
    public $gastoAEliminar = null;

    public $search = '';
    public $filtroCategoria = '';
    public $fechaDesde = '';
    public $fechaHasta = '';

    public $pendingSearch = '';
    public $pendingCategoria = '';
    public $pendingFechaDesde = '';
    public $pendingFechaHasta = '';



    protected $listeners = [
        'gastoGuardado' => 'cerrarModal',
        'cerrarModal' => 'cerrarModal',
    ];

    public function aplicarFiltros()
    {
        $this->search       = $this->pendingSearch;
        $this->filtroCategoria = $this->pendingCategoria;
        $this->fechaDesde   = $this->pendingFechaDesde;
        $this->fechaHasta   = $this->pendingFechaHasta;

        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtroCategoria = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';

        $this->pendingSearch = '';
        $this->pendingCategoria = '';
        $this->pendingFechaDesde = '';
        $this->pendingFechaHasta = '';

        $this->resetPage();
    }




    public function updatedFiltroCategoria()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }



    public function abrirModal()
    {
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->dispatch('$refresh');
    }

    public function confirmarEliminar($id)
    {
        $this->gastoAEliminar = $id;
        $this->showDeleteModal = true;
    }

    public function cancelarEliminar()
    {
        $this->showDeleteModal = false;
        $this->gastoAEliminar = null;
    }

    public function eliminar()
    {
        $gasto = GastoGeneralEmpresa::find($this->gastoAEliminar);

        if ($gasto) {
            // Eliminar archivo si existe
            if ($gasto->factura_url && Storage::disk('public')->exists($gasto->factura_url)) {
                Storage::disk('public')->delete($gasto->factura_url);
            }

            $gasto->delete();
        }

        $this->showDeleteModal = false;
        $this->gastoAEliminar = null;

        $this->dispatch('toast', type: 'success', text: 'Gasto eliminado correctamente.');
    }



    public function render()
    {
        $query = GastoGeneralEmpresa::with('categoria');

        // BUSCADOR GLOBAL
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                    ->orWhere('numero_factura', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                    ->orWhere('especificacion', 'like', '%' . $this->search . '%');
            });
        }

        // FILTRO POR CATEGORÍA
        if ($this->filtroCategoria) {
            $query->where('categoria_id', $this->filtroCategoria);
        }

        // FECHA DESDE
        if ($this->fechaDesde) {
            $query->whereDate('fecha_factura', '>=', $this->fechaDesde);
        }

        // FECHA HASTA
        if ($this->fechaHasta) {
            $query->whereDate('fecha_factura', '<=', $this->fechaHasta);
        }

        return view('livewire.empresa.gastos.index', [
            'gastos' => $query->orderBy('fecha_factura', 'desc')->paginate(10),

            // categorías para los filtros
            'categoriasPadre' => CategoriaGastoEmpresa::where('nivel', 1)
                ->with('children')
                ->ordenado()
                ->get(),
        ]);
    } 
}
