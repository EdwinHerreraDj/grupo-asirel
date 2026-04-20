<?php

namespace App\Livewire\Documentos;

use App\Models\DocumentoTipo;
use Livewire\Attributes\On;
use Livewire\Component;

class TiposModal extends Component
{
    public bool $abierto = false;
    public string $nuevoTipo = '';
    public ?int $tipoAEliminarId = null;
    public ?string $tipoAEliminarNombre = null;

    protected function rules(): array
    {
        return [
            'nuevoTipo' => 'required|string|max:255|unique:documento_tipos,nombre',
        ];
    }

    protected function messages(): array
    {
        return [
            'nuevoTipo.required' => 'Escribe el nombre del tipo.',
            'nuevoTipo.max' => 'Máximo 255 caracteres.',
            'nuevoTipo.unique' => 'Ya existe un tipo con ese nombre.',
        ];
    }

    #[On('abrir-gestion-tipos')]
    public function abrir(): void
    {
        $this->abierto = true;
        $this->nuevoTipo = '';
        $this->resetValidation();
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->nuevoTipo = '';
        $this->tipoAEliminarId = null;
        $this->tipoAEliminarNombre = null;
        $this->resetValidation();
    }

    public function confirmarEliminar(int $id): void
    {
        $tipo = DocumentoTipo::findOrFail($id);
        $this->tipoAEliminarId = $tipo->id;
        $this->tipoAEliminarNombre = $tipo->nombre;
    }

    public function cancelarEliminar(): void
    {
        $this->tipoAEliminarId = null;
        $this->tipoAEliminarNombre = null;
    }

    public function agregar(): void
    {
        $data = $this->validate();

        DocumentoTipo::create([
            'nombre' => mb_strtoupper(trim($data['nuevoTipo'])),
        ]);

        $this->nuevoTipo = '';

        $this->dispatch('tipos-actualizados');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tipo añadido correctamente.',
        ]);
    }

    public function eliminar(): void
    {
        if (! $this->tipoAEliminarId) {
            return;
        }

        $tipo = DocumentoTipo::findOrFail($this->tipoAEliminarId);

        if (! $tipo->esEliminable()) {
            $this->cancelarEliminar();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar: existen documentos que usan este tipo.',
            ]);

            return;
        }

        $tipo->delete();
        $this->cancelarEliminar();

        $this->dispatch('tipos-actualizados');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tipo eliminado.',
        ]);
    }

    public function render()
    {
        $tipos = DocumentoTipo::withCount('documentos')->orderBy('nombre')->get();

        return view('livewire.documentos.tipos-modal', compact('tipos'));
    }
}
