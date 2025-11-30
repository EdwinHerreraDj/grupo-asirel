<?php

namespace App\Livewire\Proveedores;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proveedor;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filtroActivo = '';
    public $filtrar = false;

    public $proveedorAEliminar = null;
    public $confirmarEliminacion = false;

    public $modalCrear = false;
    public $modalEditar = false;
    public $proveedorEditarId = null;

    protected $listeners = [
        'proveedorGuardado' => 'cerrarModales',
        'cerrarModalProveedor' => 'cerrarModales',
    ];

    protected $paginationTheme = 'tailwind';

    public function aplicarFiltros()
    {
        $this->filtrar = true;
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtroActivo', 'filtrar']);
    }

    public function abrirModalEditar($id)
    {
        $this->proveedorEditarId = $id;
        $this->modalEditar = true;
    }


    public function abrirModalEliminar($id)
    {
        $this->proveedorAEliminar = $id;
        $this->confirmarEliminacion = true;
    }

    public function eliminar()
    {
        Proveedor::findOrFail($this->proveedorAEliminar)->delete();

        $this->confirmarEliminacion = false;
        $this->proveedorAEliminar = null;

        $this->dispatch('toast', type: 'success', text: 'Proveedor eliminado correctamente.');
    }

    public function abrirModalCrear()
    {
        $this->modalCrear = true;
    }

    public function cerrarModales()
    {
        $this->modalCrear = false;
        $this->modalEditar = false;
    }

    public function render()
    {
        $proveedores = Proveedor::query()
            ->when($this->filtrar, function ($q) {
                $q->when(
                    $this->search,
                    fn($q) =>
                    $q->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('cif', 'like', "%{$this->search}%")
                );

                if ($this->filtroActivo !== '') {
                    $q->where('activo', $this->filtroActivo);
                }
            })
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.proveedores.index', compact('proveedores'));
    }
}
