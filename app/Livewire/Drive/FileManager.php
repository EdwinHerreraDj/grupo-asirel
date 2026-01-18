<?php

namespace App\Livewire\Drive;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\File;
use App\Livewire\Drive\FolderManager;
use Illuminate\Support\Facades\Storage;


class FileManager extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public ?int $carpetaId = null;
    public $archivo = [];
    public $tieneCaducidad = false;
    public $fechaCaducidad = null;
    public $currentFolderId;


    public function mount($carpetaId)
    {
        $this->carpetaId = $carpetaId;
    }



    /**
     * Abrir el modal.
     */
    public function openFileUploadModal($folderId = null)
    {
        $this->resetValidation();
        $this->reset(['archivo', 'tieneCaducidad', 'fechaCaducidad']);

        $this->carpetaId = $folderId ?? $this->carpetaId;

        $this->showModal = true;
    }


    public function tieneCaducidadUpdated($value)
    {
        if (!$value) {
            $this->fechaCaducidad = null;
        }
    }


    /**
     * Subir archivo.
     */
    public function subirArchivo()
    {
        $this->validate([
            'archivo' => 'required|array|min:1',
            'archivo.*' => 'file|max:512000',
            'tieneCaducidad' => 'boolean',
            'fechaCaducidad' => $this->tieneCaducidad
                ? 'required|date|after:today'
                : 'nullable',
        ]);

        $folderId = $this->carpetaId ?? 0;

        foreach ($this->archivo as $file) {

            $path = $file->store("drive/{$folderId}", 'local');

            File::create([
                'folder_id'       => $folderId,
                'usuario_id'      => auth()->id(),
                'nombre'          => $file->getClientOriginalName(),
                'ruta'            => $path,
                'tipo'            => $file->getClientOriginalExtension(),
                'tamaño'          => $file->getSize(),
                'tiene_caducidad' => $this->tieneCaducidad,
                'fecha_caducidad' => $this->tieneCaducidad ? $this->fechaCaducidad : null,
            ]);
        }

        $this->reset(['archivo', 'tieneCaducidad', 'fechaCaducidad']);

        $this->dispatch('archivoSubido')->to(\App\Livewire\Drive\FolderManager::class);
        $this->dispatch('toast', type: 'success', text: 'Archivos subidos correctamente.');

        $this->closeModal();
    }





    /* Eliminar archivos */
    public function eliminarConfirmado(): void
    {
        if (! $this->deletingId) return;

        $file = File::find($this->deletingId);
        if (! $file) {
            $this->dispatch('toast', type: 'error', text: 'El archivo ya no existe.');
            $this->cancelarEliminar();
            return;
        }

        // Eliminar del almacenamiento físico
        Storage::disk('public')->delete($file->ruta);

        // Eliminar de la base de datos
        $file->delete();

        // Refrescar listado en el padre
        $this->dispatch('archivoSubido')->to('drive.folder-manager');

        // Notificar
        $this->dispatch('toast', type: 'success', text: 'Archivo eliminado correctamente.');

        $this->cancelarEliminar();
    }



    /**
     * Cerrar modal.
     */
    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.drive.file-manager');
    }
}
