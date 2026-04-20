<?php

namespace App\Livewire\Documentos;

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Obra;
use App\Services\DocumentoService;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public Obra $obra;
    public ?int $documentoAEliminarId = null;

    public function mount(Obra $obra): void
    {
        $this->obra = $obra;
    }

    #[On('documento-guardado')]
    #[On('tipos-actualizados')]
    public function refrescar(): void
    {
        // Livewire re-renderiza autom\u00e1ticamente
    }

    public function abrirEliminar(int $id): void
    {
        $this->documentoAEliminarId = $id;
    }

    public function cancelarEliminar(): void
    {
        $this->documentoAEliminarId = null;
    }

    public function eliminar(DocumentoService $service): void
    {
        if (! $this->documentoAEliminarId) {
            return;
        }

        $documento = Documento::findOrFail($this->documentoAEliminarId);
        $service->eliminar($documento);

        $this->documentoAEliminarId = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Documento eliminado correctamente.',
        ]);
    }

    public function render()
    {
        $documentos = Documento::with('documentoTipo')
            ->where('obra_id', $this->obra->id)
            ->orderByDesc('created_at')
            ->get();

        $tiposUsadosIds = $documentos->pluck('documento_tipo_id')->filter()->unique();

        $tiposPendientes = DocumentoTipo::whereNotIn('id', $tiposUsadosIds)
            ->orderBy('nombre')
            ->get();

        return view('livewire.documentos.index', [
            'documentos' => $documentos,
            'tiposPendientes' => $tiposPendientes,
        ]);
    }
}
