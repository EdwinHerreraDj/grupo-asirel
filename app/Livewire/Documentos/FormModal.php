<?php

namespace App\Livewire\Documentos;

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Obra;
use App\Services\DocumentoService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

class FormModal extends Component
{
    use WithFileUploads;

    public Obra $obra;
    public bool $abierto = false;
    public string $modo = 'crear'; // 'crear' | 'reemplazar'
    public ?int $tipoId = null;
    public ?int $documentoId = null;
    public ?string $fechaVencimiento = null;
    public $archivo = null;

    public function mount(Obra $obra): void
    {
        $this->obra = $obra;
    }

    protected function rules(): array
    {
        return [
            'tipoId' => 'required|integer|exists:documento_tipos,id',
            'archivo' => 'required|file|mimes:pdf|max:20480',
            'fechaVencimiento' => 'nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'tipoId.required' => 'Selecciona un tipo de documento.',
            'tipoId.exists' => 'El tipo seleccionado no existe.',
            'archivo.required' => 'Selecciona un archivo PDF.',
            'archivo.mimes' => 'Solo se permiten archivos PDF.',
            'archivo.max' => 'El archivo no puede superar los 20 MB.',
            'fechaVencimiento.date' => 'La fecha de vencimiento no es válida.',
        ];
    }

    #[On('abrir-form-documento')]
    public function abrir(?int $tipoId = null, ?int $documentoId = null): void
    {
        $this->resetValidation();
        $this->archivo = null;
        $this->fechaVencimiento = null;

        if ($documentoId) {
            $this->modo = 'reemplazar';
            $documento = Documento::findOrFail($documentoId);
            $this->documentoId = $documento->id;
            $this->tipoId = $documento->documento_tipo_id;
            $this->fechaVencimiento = $documento->fecha_vencimiento?->format('Y-m-d');
        } else {
            $this->modo = 'crear';
            $this->documentoId = null;
            $this->tipoId = $tipoId;
        }

        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->archivo = null;
        $this->fechaVencimiento = null;
        $this->documentoId = null;
        $this->tipoId = null;
        $this->modo = 'crear';
        $this->resetValidation();
    }

    public function guardar(DocumentoService $service): void
    {
        $this->validate();

        try {
            $vencimiento = $this->fechaVencimiento ? Carbon::parse($this->fechaVencimiento) : null;

            if ($this->modo === 'reemplazar') {
                $documento = Documento::findOrFail($this->documentoId);
                $service->reemplazar($documento, $this->archivo, $vencimiento);
                $mensaje = 'Documento reemplazado correctamente.';
            } else {
                $tipo = DocumentoTipo::findOrFail($this->tipoId);
                $service->subir($this->obra, $tipo, $this->archivo, $vencimiento);
                $mensaje = 'Documento subido correctamente.';
            }
        } catch (RuntimeException $e) {
            $this->addError('archivo', $e->getMessage());

            return;
        }

        $this->cerrar();
        $this->dispatch('documento-guardado');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $mensaje,
        ]);
    }

    public function render()
    {
        $tipos = DocumentoTipo::orderBy('nombre')->get();

        return view('livewire.documentos.form-modal', compact('tipos'));
    }
}
