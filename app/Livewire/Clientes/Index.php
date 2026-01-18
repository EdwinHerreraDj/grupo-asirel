<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public int $perPage = 10;

    public $modalFormulario = false;
    public $confirmarEliminacion = false;
    public $clienteAEliminar = null;
    public $clienteEditarId = null;

    // Inputs del formulario (NO filtran directamente)
    public string $search = '';
    public string $filtroActivo = '';

    // Filtros realmente aplicados
    public string $searchAplicado = '';
    public string $estadoAplicado = '';


    protected $listeners = [
        'clienteGuardado' => 'cerrarModales',
        'cerrarModalCliente' => 'cerrarModales',
    ];


    public function abrirModalCrear()
    {
        $this->modalFormulario = true;
        $this->dispatch('crearCliente');
    }

    public function abrirModalEditar($id)
    {
        $this->dispatch('editarCliente', id: $id);
        $this->modalFormulario = true;
    }


    public function abrirModalEliminar($id)
    {
        $this->clienteAEliminar = $id;
        $this->confirmarEliminacion = true;
    }

    public function cerrarModales()
    {
        $this->modalFormulario = false;
    }


    public function eliminar()
    {
        if ($this->clienteAEliminar) {
            $cliente = Cliente::find($this->clienteAEliminar);
            if ($cliente) {
                $cliente->delete();
            }
        }
        $this->confirmarEliminacion = false;
        $this->clienteAEliminar = null;
        $this->dispatch('toast', type: 'success', text: 'Cliente eliminado correctamente.');
    }



    public function aplicarFiltros()
    {
        $this->searchAplicado = $this->search;
        $this->estadoAplicado = $this->filtroActivo;

        // Reiniciar paginación
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'search',
            'filtroActivo',
            'searchAplicado',
            'estadoAplicado',
        ]);

        $this->resetPage();
    }



    public function render()
    {
        $clientes = Cliente::query()

            ->when($this->searchAplicado !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->searchAplicado . '%')
                        ->orWhere('cif', 'like', '%' . $this->searchAplicado . '%')
                        ->orWhere('email', 'like', '%' . $this->searchAplicado . '%')
                        ->orWhere('telefono', 'like', '%' . $this->searchAplicado . '%');
                });
            })

            ->when($this->estadoAplicado !== '', function ($q) {
                $q->where('activo', $this->estadoAplicado);
            })

            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.clientes.index', compact('clientes'));
    }
}
