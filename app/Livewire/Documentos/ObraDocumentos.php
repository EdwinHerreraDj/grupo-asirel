<?php

namespace App\Livewire\Documentos;

use App\Models\Documento;
use App\Models\Obra;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ObraDocumentos extends Component
{
    use WithFileUploads;

    public $obra;

    public $tipo;

    public $archivo;

    protected $listeners = ['eliminarDocumento'];

    protected $rules = [
        'tipo' => 'required|string|max:255',
        'archivo' => 'required|file|mimes:pdf,jpg,png,docx,zip,rar|max:20480',
    ];

    public function mount(Obra $obra)
    {
        $this->obra = $obra;
    }

    public function subirDocumento()
    {
        $this->validate();

        if (Documento::where('obra_id', $this->obra->id)->where('tipo', $this->tipo)->exists()) {
            $this->dispatch('notificar', [
                'type' => 'error',
                'message' => 'Ya existe un documento de este tipo para esta obra.',
            ]);

            return;
        }

        $nombreOriginal = pathinfo($this->archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $this->archivo->getClientOriginalExtension();
        $nombreUnico = $nombreOriginal.'_'.time().'.'.$extension;
        $ruta = $this->archivo->storeAs('documentos', $nombreUnico, 'public');

        Documento::create([
            'obra_id' => $this->obra->id,
            'tipo' => $this->tipo,
            'archivo' => $ruta,
        ]);

        $this->reset(['tipo', 'archivo']);

        $this->dispatch('notificar', [
            'type' => 'success',
            'message' => 'Documento subido correctamente.',
        ]);
    }

    public function eliminarDocumento($id)
    {
        $documento = Documento::findOrFail($id);
        Storage::disk('public')->delete($documento->archivo);
        $documento->delete();

        $this->dispatch('notificar', [
            'type' => 'success',
            'message' => 'Documento eliminado correctamente.',
        ]);
    }

    public function render()
    {
        $documentos = Documento::where('obra_id', $this->obra->id)->get();

        return view('livewire.documentos.obra-documentos', compact('documentos'));
    }
}
