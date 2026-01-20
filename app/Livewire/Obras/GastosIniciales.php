<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\ObraGastoCategoria;

class GastosIniciales extends Component
{
    public $obra;
    public $categorias;
    public $gastosObra = [];

    // Modales
    public $showModal = false;
    public $showModalEliminar = false;

    // Campos categoría
    public $categoria_id;
    public $nombre_categoria;
    public $descripcion_categoria;

    public $mensajeErrorEliminar = null;

    public $modo = 'crear';

    protected $rules = [
        'nombre_categoria' => 'required|string|max:255',
        'descripcion_categoria' => 'nullable|string',
    ];

    public function mount($obra = null)
    {
        $this->obra = $obra;

        if ($this->obra) {

            // Categorías de esta obra (orden numérico + nombre)
            $this->categorias = $this->obra->categoriasGasto()
                ->orderByRaw("
                CAST(
                    SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED
                ) ASC
            ")
                ->orderBy('nombre')
                ->get();

            // Gastos iniciales existentes
            $this->gastosObra = $this->obra->gastosIniciales
                ->pluck('pivot.importe', 'id')
                ->toArray();
        } else {
            // Crear obra → aún no hay categorías
            $this->categorias = collect();
        }
    }


    public function cargarCategorias()
    {
        if (!$this->obra) {
            return;
        }

        $this->categorias = $this->obra->categoriasGasto()
            ->orderByRaw("
            CAST(
                SUBSTRING_INDEX(nombre, '-', 1) AS UNSIGNED
            ) ASC
        ")
            ->orderBy('nombre')
            ->get();
    }


    /* -------------------------------------------------------
     * CREAR NUEVA CATEGORIA
     * ------------------------------------------------------*/
    public function abrirModalCrear()
    {
        if (!$this->obra) {
            $this->addError('obra', 'Debes guardar la obra antes de crear categorías.');
            return;
        }

        $this->reset(['categoria_id', 'nombre_categoria', 'descripcion_categoria']);
        $this->modo = 'crear';
        $this->showModal = true;
    }

    public function crearCategoria()
    {
        $this->validate();

        ObraGastoCategoria::create([
            'obra_id' => $this->obra->id,
            'nombre' => $this->nombre_categoria,
            'descripcion' => $this->descripcion_categoria,
        ]);

        $this->showModal = false;
        $this->cargarCategorias();
    }

    /* -------------------------------------------------------
     * EDITAR CATEGORIA
     * ------------------------------------------------------*/
    public function abrirModalEditar($id)
    {
        $cat = ObraGastoCategoria::findOrFail($id);

        $this->categoria_id = $id;
        $this->nombre_categoria = $cat->nombre;
        $this->descripcion_categoria = $cat->descripcion;
        $this->modo = 'editar';
        $this->showModal = true;
    }

    public function actualizarCategoria()
    {
        $this->validate();

        ObraGastoCategoria::findOrFail($this->categoria_id)
            ->update([
                'nombre' => $this->nombre_categoria,
                'descripcion' => $this->descripcion_categoria,
            ]);

        $this->showModal = false;
        $this->cargarCategorias();
    }

    /* -------------------------------------------------------
     * ELIMINAR CATEGORIA
     * ------------------------------------------------------*/
    public function eliminarCategoria($id, $aplicar = false)
    {
        if (!$aplicar) {
            // Abrir modal
            $this->categoria_id = $id;
            $this->mensajeErrorEliminar = null;
            $this->showModalEliminar = true;
            return;
        }

        // Validar si está usada
        $usada = DB::table('obra_gastos_iniciales')
            ->where('obra_gasto_categoria_id', $id)
            ->exists();

        if ($usada) {
            $this->mensajeErrorEliminar =
                'No se puede eliminar porque está asociada a los gastos iniciales.';
            return;
        }

        // Eliminar categoría
        ObraGastoCategoria::findOrFail($id)->delete();

        // Reset
        $this->showModalEliminar = false;
        $this->mensajeErrorEliminar = null;

        // Recargar lista
        $this->cargarCategorias();
    }

    /* -------------------------------------------------------
     * RENDER
     * ------------------------------------------------------*/
    public function render()
    {
        return view('livewire.obras.gastos-iniciales', [
            'categorias' => $this->categorias,
            'gastosObra' => $this->gastosObra,
            'obra' => $this->obra,
        ]);
    }
}
