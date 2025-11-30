<?php

namespace App\Livewire\Empresa\Gastos\Categorias;

use Livewire\Component;
use App\Models\CategoriaGastoEmpresa;
use App\Livewire\Empresa\Gastos\Categorias\Index;

class Formulario extends Component
{
    public $categoria_id = null;
    public $codigo;
    public $nombre;
    public $descripcion;
    public $parent_id = null;
    public $nivel = 1;
    public $tieneHijos = false;

    public $modo = 'crear';
    public $esModal = false;
    public $parentId = null;    // se recibe desde el Index

    protected $rules = [
        'nombre' => 'required|string|max:150',
        'descripcion' => 'nullable|string',
        'parent_id' => 'nullable|integer|exists:categorias_gastos_empresa,id',
    ];

    public function mount($id = null, $modo = 'crear', $parentId = null, $esModal = false)
    {
        $this->modo = $modo;
        $this->esModal  = $esModal;
        $this->parentId = $parentId;

        // ----------------------------
        // MODO EDITAR
        // ----------------------------
        if ($id) {
            $this->categoria_id = $id;
            $cat = CategoriaGastoEmpresa::findOrFail($id);

            $this->codigo = $cat->codigo;
            $this->nombre = $cat->nombre;
            $this->descripcion = $cat->descripcion;
            $this->parent_id = $cat->parent_id;
            $this->nivel = $cat->nivel;


            // ¿Tiene hijos esta categoría?
            $this->tieneHijos = $cat->children()->exists();

            return;
        }

        // ----------------------------
        // MODO CREAR SUBCATEGORÍA
        // ----------------------------
        if ($this->parentId) {
            $this->parent_id = $this->parentId;
            $this->nivel = 2;
            $this->codigo = $this->generarCodigo();
            return;
        }

        // ----------------------------
        // MODO CREAR CATEGORÍA PADRE
        // ----------------------------
        $this->nivel = 1;
        $this->codigo = $this->generarCodigo();
    }


    /**
     * Al cambiar el padre manualmente en el select
     */
    public function updatedParentId()
    {
        if ($this->parent_id === "" || $this->parent_id === null) {
            $this->parent_id = null;
            $this->nivel = 1;
        } else {
            $this->nivel = 2;
        }

        $this->codigo = $this->generarCodigo();
    }


    /**
     * Genera códigos contables reales.
     * Padre: 001, 002, 003...
     * Hija:  001.001, 001.002, 002.001...
     */
    public function generarCodigo()
    {
        // ----------------------------
        // SUBCATEGORÍA
        // ----------------------------
        if ($this->parent_id) {
            $padre = CategoriaGastoEmpresa::find($this->parent_id);

            // Última subcategoría de ese padre
            $ultimo = CategoriaGastoEmpresa::where('parent_id', $padre->id)
                ->orderBy('codigo', 'desc')
                ->first();

            if ($ultimo) {
                $partes = explode('.', $ultimo->codigo); // ej: [001, 005]
                $last = intval($partes[1]) + 1;
                $bloque = str_pad($last, 3, '0', STR_PAD_LEFT);
            } else {
                $bloque = '001';
            }

            return $padre->codigo . '.' . $bloque;
        }

        // ----------------------------
        // CATEGORÍA PADRE
        // ----------------------------
        $ultimoPadre = CategoriaGastoEmpresa::whereNull('parent_id')
            ->orderBy('codigo', 'desc')
            ->first();

        if ($ultimoPadre) {
            $new = intval($ultimoPadre->codigo) + 1;
            $prefijo = str_pad($new, 3, '0', STR_PAD_LEFT);
        } else {
            $prefijo = '001';
        }

        return $prefijo;
    }


    public function guardar()
    {
        $this->validate();

        // Evitar enviar "" como parent_id (error SQL)
        if ($this->parent_id === "" || $this->parent_id === null) {
            $this->parent_id = null;
        }

        // Si tiene hijos → NO permitir cambiar parent_id
        if ($this->tieneHijos && $this->parent_id !== null) {
            $this->dispatch(
                'toast',
                type: 'error',
                text: 'Esta categoría tiene subcategorías. No puedes convertirla en subcategoría.'
            );
            return;
        }


        CategoriaGastoEmpresa::updateOrCreate(
            ['id' => $this->categoria_id],
            [
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'parent_id' => $this->parent_id,
                'nivel' => $this->nivel,
            ]
        );

        // Notificación global
        $this->dispatch(
            'toast',
            type: 'success',
            text: $this->categoria_id ? 'Categoría actualizada' : 'Categoría creada'
        );

        if ($this->esModal) {
            $this->dispatch('categoriaGuardada')->to(Index::class);
            return;
        }
    }


    public function render()
    {
        return view('livewire.empresa.gastos.categorias.formulario', [
            'categoriasPadre' => CategoriaGastoEmpresa::whereNull('parent_id')->orderBy('codigo')->get(),
        ]);
    }
}
