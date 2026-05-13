<?php

namespace App\Livewire\Obras;

use App\Models\Obra;
use App\Services\ObraService;
use Livewire\Attributes\On;
use Livewire\Component;

class FormModal extends Component
{
    public bool $abierto = false;
    public ?int $obraId = null;

    public string $nombre = '';
    public string $estado = 'planificacion';
    public string $tipo = 'subcontratista';
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public $importe_presupuestado = null;
    public ?string $descripcion = null;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'estado' => 'required|string|in:planificacion,ejecucion,en_pausa,finalizada',
            'tipo' => 'required|string|in:subcontratista,contratista',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'importe_presupuestado' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
        ];
    }

    #[On('abrir-form-obra')]
    public function abrir(?int $id = null): void
    {
        $this->resetValidation();
        $this->obraId = $id;

        if ($id) {
            $obra = Obra::findOrFail($id);
            $this->nombre = $obra->nombre;
            $this->estado = $obra->estado;
            $this->tipo = $obra->tipo ?? 'subcontratista';
            $this->fecha_inicio = $obra->fecha_inicio?->format('Y-m-d');
            $this->fecha_fin = $obra->fecha_fin?->format('Y-m-d');
            $this->importe_presupuestado = $obra->importe_presupuestado;
            $this->descripcion = $obra->descripcion;
        } else {
            $this->reiniciarCampos();
        }

        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->reiniciarCampos();
        $this->resetValidation();
    }

    public function guardar(ObraService $service): void
    {
        $data = $this->validate();

        if ($this->obraId) {
            $obra = Obra::findOrFail($this->obraId);
            $service->actualizar($obra, $data);
            $mensaje = 'Obra actualizada correctamente.';
        } else {
            $service->crear($data);
            $mensaje = 'Obra creada correctamente.';
        }

        $this->cerrar();

        $this->dispatch('obra-guardada');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $mensaje,
        ]);
    }

    private function reiniciarCampos(): void
    {
        $this->obraId = null;
        $this->nombre = '';
        $this->estado = 'planificacion';
        $this->tipo = 'subcontratista';
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->importe_presupuestado = null;
        $this->descripcion = null;
    }

    public function render()
    {
        return view('livewire.obras.form-modal');
    }
}
