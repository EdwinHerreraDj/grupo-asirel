<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Formulario extends Component
{
    public ?int $cliente_id = null;

    public string $nombre = '';
    public ?string $cif = null;
    public ?string $email = null;
    public ?string $telefono = null;
    public ?string $direccion = null;
    public ?string $descripcion = null;
    public bool $activo = true;

    protected $listeners = [
        'crearCliente' => 'nuevo',
        'editarCliente' => 'editar',
    ];

    /* =====================
        VALIDACIÓN
    ===================== */
    protected function rules()
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'cif' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('clientes', 'cif')->ignore($this->cliente_id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['boolean'],
        ];
    }

    /* =====================
        ACCIONES
    ===================== */

    /** Crear cliente */
    public function nuevo()
    {
        $this->resetForm();
    }

    /** Editar cliente */
    public function editar(int $id)
    {
        $c = Cliente::findOrFail($id);

        $this->cliente_id = $c->id;
        $this->nombre = $c->nombre;
        $this->cif = $c->cif;
        $this->email = $c->email;
        $this->telefono = $c->telefono;
        $this->direccion = $c->direccion;
        $this->descripcion = $c->descripcion;
        $this->activo = (bool) $c->activo;
    }

    /** Guardar */
    public function guardar()
    {
        $data = $this->validate();

        Cliente::updateOrCreate(
            ['id' => $this->cliente_id],
            $data
        );

        // Refrescar listado
        $this->dispatch('clienteActualizado');

        // Cerrar modal
        $this->dispatch('cerrarModalCliente');

        // Notificación
        $this->dispatch(
            'toast',
            type: 'success',
            text: $this->cliente_id ? 'Cliente actualizado' : 'Cliente creado'
        );

        // LIMPIAR FORMULARIO para la próxima apertura
        $this->resetForm();
    }

    /* =====================
        HELPERS
    ===================== */

    private function resetForm()
    {
        $this->reset([
            'cliente_id',
            'nombre',
            'cif',
            'email',
            'telefono',
            'direccion',
            'descripcion',
        ]);

        $this->activo = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clientes.formulario');
    }
}
