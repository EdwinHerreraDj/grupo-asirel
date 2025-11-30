<?php

namespace App\Livewire\Empresa\Gastos\Categorias;

use Livewire\Component;
use App\Models\CategoriaGastoEmpresa;

class Index extends Component
{
    public $categoriasPadre = [];
    public $categoriaEditar = null;
    public $categoriaCrearEnPadre = null;

    public $mostrarModal = false;
    public $modoFormulario = 'crear';
    public $mostrarModalEliminar = false;
    public $categoriaEliminarId = null;
    public $categoria_id = null;

    protected $listeners = [
        'categoriaGuardada' => 'actualizarListado',
        'eliminarCategoria' => 'eliminar',
    ];

    public function mount()
    {
        $this->actualizarListado();
    }

    public function actualizarListado()
    {
        $this->cerrarModal();
        $this->categoriasPadre = CategoriaGastoEmpresa::padres();
    }


    /**
     * Crear categoría padre
     */
    public function crearCategoriaPadre()
    {
        $this->categoria_id = null;
        $this->modoFormulario = 'crear';
        $this->mostrarModal = true;
    }

    /**
     * Crear subcategoría de un padre
     */
    public function crearSubcategoria($parentId)
    {
        $this->categoria_id = null;
        $this->categoriaCrearEnPadre = $parentId;
        $this->modoFormulario = 'crear_sub';
        $this->mostrarModal = true;
    }

    /**
     * Editar categoría
     */
    public function editar($id)
    {

        $this->categoria_id = $id;
        $this->modoFormulario = 'editar';
        $this->categoriaCrearEnPadre = null;
        $this->mostrarModal = true;
    }


    /**
     * Eliminar categoría
     */
    public function confirmarEliminar($id)
    {
        $this->categoriaEliminarId = $id;
        $this->mostrarModalEliminar = true;
    }


    public function eliminar()
    {
        

        $cat = CategoriaGastoEmpresa::find($this->categoriaEliminarId);

        if (!$cat) {
            $this->dispatch('toast', type: 'error', text: 'La categoría no existe.');
            return;
        }

        if ($cat->children()->count() > 0) {
            $this->dispatch('toast', type: 'error', text: 'No puedes eliminar una categoría que tiene subcategorías.');
            return;
        }

        $cat->delete();

        $this->dispatch('toast', type: 'success', text: 'Categoría eliminada correctamente.');

        $this->mostrarModalEliminar = false;
        $this->actualizarListado();
    }



    /**
     * Cerrar modal desde botón X
     */
    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->categoria_id = null;
        $this->categoriaCrearEnPadre = null;
    }

    public function render()
    {
        return view('livewire.empresa.gastos.categorias.index', [
            'categorias' => $this->categoriasPadre,
        ]);
    }
}
