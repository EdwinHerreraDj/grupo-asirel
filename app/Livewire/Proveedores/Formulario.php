<?php

namespace App\Livewire\Proveedores;

use Livewire\Component;
use App\Models\Proveedor;

class Formulario extends Component
{
    public $proveedor_id = null;
    public $nombre, $cif, $telefono, $email, $direccion, $tipo, $activo = true;

    // modo: 'crear' | 'editar' | 'modal'
    public $modo = 'crear';

    protected $rules = [
        'nombre' => 'required|string|max:150',
        'cif' => 'nullable|string|max:20',
        'telefono' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:150',
        'direccion' => 'nullable|string|max:255',
        'tipo' => 'nullable|string|max:100',
        'activo' => 'boolean',
    ];

    public function mount($id = null, $modo = 'crear')
    {
        $this->modo = $modo;

        // Si es editar o modal con ID → carga datos
        if ($id) {
            $this->proveedor_id = $id;
            $p = Proveedor::findOrFail($id);

            $this->nombre = $p->nombre;
            $this->cif = $p->cif;
            $this->telefono = $p->telefono;
            $this->email = $p->email;
            $this->direccion = $p->direccion;
            $this->tipo = $p->tipo;
            $this->activo = (bool) $p->activo;
        }
    }

    public function guardar()
    {
        $this->validate();

        Proveedor::updateOrCreate(
            ['id' => $this->proveedor_id],
            [
                'nombre' => $this->nombre,
                'cif' => $this->cif,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'tipo' => $this->tipo,
                'activo' => $this->activo,
            ]
        );

        // Notificación
        $this->dispatch(
            'toast',
            type: 'success',
            text: $this->proveedor_id ? 'Proveedor actualizado' : 'Proveedor creado'
        );

        // Si está en modal → emitir evento para cerrar modal + refrescar lista
        if ($this->modo === 'modal') {
            $this->dispatch('proveedorGuardado');
            return;
        }

        // Si está en página → redirigir
        return redirect()->route('proveedores');
    }

    public function render()
    {
        return view('livewire.proveedores.formulario');
    }
}
