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
    public $archivo;
    public $tieneCaducidad = false;
    public $fechaCaducidad;
    public $currentFolderId;

    protected $rules = [
        'archivo' => 'required|file|max:10240',
        'fechaCaducidad' => 'nullable|date|after:today',
    ];

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
            'archivo' => 'required|file|max:51200', // máximo 50 MB
            'tieneCaducidad' => 'boolean',
            'fechaCaducidad' => 'nullable|date|after:today',
        ]);

        // Carpeta actual o raíz
        $folderId = $this->carpetaId ?? 0;

        // Almacenar el archivo en disco local
        $path = $this->archivo->store("drive/{$folderId}", 'local');

        // Crear registro en base de datos
        File::create([
            'folder_id'        => $folderId,
            'usuario_id'       => auth()->id(),
            'nombre'           => $this->archivo->getClientOriginalName(),
            'ruta'             => $path,
            'tipo'             => $this->archivo->getClientOriginalExtension(),
            'tamaño'           => $this->archivo->getSize(),
            'tiene_caducidad'  => $this->tieneCaducidad ?? false,
            'fecha_caducidad'  => $this->tieneCaducidad ? $this->fechaCaducidad : null,
        ]);

        // Limpiar inputs y notificar
        $this->reset(['archivo', 'tieneCaducidad', 'fechaCaducidad']);
        $this->dispatch('toast', type: 'success', text: 'Archivo subido correctamente.');

        // Cerrar modal si existe
        $this->dispatch('archivoSubido')->to(FolderManager::class);
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
