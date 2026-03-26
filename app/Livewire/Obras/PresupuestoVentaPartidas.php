<?php

namespace App\Livewire\Obras;

use Livewire\Component;
use App\Models\ObraPresupuestoVenta;
use App\Models\PresupuestoVentaPartida;

class PresupuestoVentaPartidas extends Component
{
    public ObraPresupuestoVenta $capitulo;

    public array $partidas = [];

    public ?int $editandoId = null;
    public bool $creandoNueva = false;

    public array $form = [
        'codigo'          => '',
        'descripcion'     => '',
        'unidad'          => '',
        'medicion'        => '',
        'precio_unitario' => '',
    ];

    public function mount(ObraPresupuestoVenta $capitulo): void
    {
        $this->capitulo = $capitulo;
        $this->cargarPartidas();
    }

    private function cargarPartidas(): void
    {
        $this->partidas = $this->capitulo
            ->partidas()
            ->get()
            ->toArray();
    }

    // -------------------------
    // CREAR
    // -------------------------

    public function nuevaPartida(): void
    {
        $this->resetForm();
        $this->creandoNueva = true;
        $this->editandoId = null;
    }

    // -------------------------
    // EDITAR
    // -------------------------

    public function editarPartida(int $id): void
    {
        $partida = collect($this->partidas)->firstWhere('id', $id);

        if (!$partida) return;

        $this->editandoId   = $id;
        $this->creandoNueva = false;

        $this->form = [
            'codigo'          => $partida['codigo'] ?? '',
            'descripcion'     => $partida['descripcion'],
            'unidad'          => $partida['unidad'] ?? '',
            'medicion'        => $partida['medicion'],
            'precio_unitario' => $partida['precio_unitario'],
        ];
    }

    // -------------------------
    // GUARDAR (crear o editar)
    // -------------------------

    public function guardarPartida(): void
    {
        $this->validate([
            'form.descripcion'     => 'required|string|max:500',
            'form.medicion'        => 'required|numeric|min:0',
            'form.precio_unitario' => 'required|numeric|min:0',
            'form.unidad'          => 'nullable|string|max:50',
            'form.codigo'          => 'nullable|string|max:50',
        ]);

        if ($this->editandoId) {
            PresupuestoVentaPartida::findOrFail($this->editandoId)
                ->update($this->form);
        } else {
            PresupuestoVentaPartida::create([
                ...$this->form,
                'obra_presupuesto_venta_id' => $this->capitulo->id,
                'obra_id'                   => $this->capitulo->obra_id,
                'orden'                     => $this->capitulo->partidas()->count(),
            ]);
        }

        $this->cancelar();
        $this->cargarPartidas();

        // Notificar al padre para que refresque totales
        $this->dispatch('partida-actualizada');
        $this->dispatch('toast', type: 'success', text: 'Partida guardada correctamente.');
    }

    // -------------------------
    // ELIMINAR
    // -------------------------

    public function eliminarPartida(int $id): void
    {
        $partida = PresupuestoVentaPartida::findOrFail($id);

        // Cuando lleguemos a certificaciones, aquí irá la comprobación
        // de si tiene líneas vinculadas. Por ahora eliminación directa.
        $partida->delete();

        $this->cargarPartidas();
        $this->dispatch('partida-actualizada');
        $this->dispatch('toast', type: 'success', text: 'Partida eliminada.');
    }

    // -------------------------
    // CANCELAR / RESET
    // -------------------------

    public function cancelar(): void
    {
        $this->editandoId   = null;
        $this->creandoNueva = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->form = [
            'codigo'          => '',
            'descripcion'     => '',
            'unidad'          => '',
            'medicion'        => '',
            'precio_unitario' => '',
        ];
    }

    public function render()
    {
        return view('livewire.obras.presupuesto-venta-partidas');
    }
}
