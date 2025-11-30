<?php

namespace App\Livewire\Drive;

use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;


class FolderManager extends Component
{
    public string $nuevoNombre = '';

    public bool $mostrarFormulario = false;

    public int $currentFolderId = 0;

    public ?Folder $currentFolder = null;

    public ?int $renamingId = null;

    public string $nombreEditado = '';

    public ?int $deletingId = null;

    public bool $showDeleteModal = false;

    public ?string $deletingName = null;

    public ?string $deleteError = null;

    protected array $cache = [];

    /* Propiedades para los Files */
    public ?int $deletingFileId = null;
    public ?string $deletingFileName = null;
    public bool $showDeleteFileModal = false;
    public ?string $deleteFileError = null;

    /* Propiedades para poder filtrar carpetas y archivos */
    public string $search = '';
    public bool $searching = false;


    /* Propiedades para mover contenudios entre carpetas */
    public ?int $movingId = null; 
    public ?string $movingType = null; 
    public bool $showMoveModal = false; 
    public ?int $destinationFolderId = null;



    public function mount()
    {
        $this->setCurrentFolder(0);
    }

    public function buscar()
{
    $this->searching = true;
}

    private function setCurrentFolder(int $id): void
    {
        $this->currentFolderId = $id;
        $this->currentFolder = $id === 0 ? null : Folder::find($id);
    }

    public function toggleFormulario()
    {
        $this->mostrarFormulario = ! $this->mostrarFormulario;
        $this->nuevoNombre = '';
    }

    public function crearCarpeta()
    {
        $this->validate([
            'nuevoNombre' => 'required|string|min:1|max:150',
        ]);

        $existe = Folder::where('parent_id', $this->currentFolderId)
            ->where('nombre', trim($this->nuevoNombre))
            ->exists();

        if ($existe) {
            $this->dispatch('toast', type: 'error', text: 'Ya existe una carpeta con ese nombre.');

            return;
        }

        $folder = Folder::create([
            'nombre' => trim($this->nuevoNombre),
            'parent_id' => $this->currentFolderId,
            'usuario_id' => Auth::id(),
            'tipo' => $this->currentFolderId === 0 ? 1 : 2,
        ]);

        Storage::disk('local')->makeDirectory("drive/{$folder->id}");

        $this->nuevoNombre = '';
        $this->mostrarFormulario = false;
        $this->dispatch('toast', type: 'success', text: 'Carpeta creada correctamente.');
    }

    public function abrirCarpeta(int $id)
    {
        $this->setCurrentFolder($id);
        $this->dispatch('carpetaCambiada', $id);
    }

    public function subirNivel()
    {
        if ($this->currentFolder?->parent_id) {
            $this->setCurrentFolder($this->currentFolder->parent_id);
        } else {
            $this->setCurrentFolder(0);
        }
    
        $this->dispatch('carpetaCambiada', $this->currentFolderId);
    }


    // INICIAR RENOMBRE
    public function iniciarRenombrar(int $id)
    {
        $folder = Folder::findOrFail($id);
        $this->renamingId = $id;
        $this->nombreEditado = $folder->nombre;
    }

    // GUARDAR RENOMBRE
    public function guardarRenombrar()
    {
        $this->validate([
            'nombreEditado' => 'required|string|min:1|max:150',
        ]);

        $folder = Folder::findOrFail($this->renamingId);

        // Validar duplicado
        $existe = Folder::where('parent_id', $folder->parent_id)
            ->where('nombre', trim($this->nombreEditado))
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($existe) {
            $this->dispatch('toast', type: 'error', text: 'Ya existe una carpeta con ese nombre.');

            return;
        }

        $folder->update(['nombre' => trim($this->nombreEditado)]);
        $this->renamingId = null;
        $this->nombreEditado = '';

        $this->dispatch('toast', type: 'success', text: 'Nombre actualizado correctamente.');
    }

    public function confirmarEliminar(int $id): void
    {
        $folder = Folder::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $folder->nombre;
        $this->showDeleteModal = true;
    }

    public function cancelarEliminar(): void
    {
        $this->reset(['showDeleteModal', 'deletingId', 'deletingName', 'deleteError']);
    }

    public function eliminarConfirmado(): void
    {
        $this->deleteError = null;

        if (! $this->deletingId) {
            return;
        }

        $folder = Folder::find($this->deletingId);
        if (! $folder) {
            $this->dispatch('toast', type: 'error', text: 'La carpeta ya no existe.');
            $this->cancelarEliminar();
            return;
        }

        // Evitar borrar si tiene subcarpetas
        if ($folder->children()->exists()) {
            $this->deleteError = 'Primero elimina las subcarpetas de esta carpeta.';
            return;
        }

        // Eliminar archivos asociados (físico + BD)
        $archivos = File::where('folder_id', $folder->id)->get();

        foreach ($archivos as $archivo) {
            // Eliminar del disco si existe
            if (Storage::disk('local')->exists($archivo->ruta)) {
                Storage::disk('local')->delete($archivo->ruta);
            }
            // Eliminar de BD
            $archivo->delete();
        }

        // Eliminar carpeta física
        Storage::disk('local')->deleteDirectory("drive/{$folder->id}");

        // Eliminar carpeta de BD
        $folder->delete();

        // Notificación
        $this->dispatch('toast', type: 'success', text: 'Carpeta y archivos eliminados.');

        // Cerrar modal y refrescar vista
        $this->cancelarEliminar();
        unset($this->cache[$this->currentFolderId]);
        $this->dispatch('$refresh');
    }

    /* ZONA DE ARCHIVOS */
    #[On('archivoSubido')]
    public function actualizarListas()
    {
        // Limpiar caché para recargar datos
        $this->cache = [];
    }

    /* Eliminar archivos */
    // 🔹 Mostrar modal de confirmación
    public function confirmarEliminarArchivo(int $id): void
    {
        $file = File::findOrFail($id);
        $this->deletingFileId = $id;
        $this->deletingFileName = $file->nombre;
        $this->showDeleteFileModal = true;
    }

    // 🔹 Cancelar acción
    public function cancelarEliminarArchivo(): void
    {
        $this->reset(['showDeleteFileModal', 'deletingFileId', 'deletingFileName', 'deleteFileError']);
    }

    // 🔹 Eliminar archivo confirmado
    public function eliminarArchivoConfirmado(): void
    {
        if (! $this->deletingFileId) return;

        $file = File::find($this->deletingFileId);
        if (! $file) {
            $this->dispatch('toast', type: 'error', text: 'El archivo ya no existe.');
            $this->cancelarEliminarArchivo();
            return;
        }

        // Eliminar del disco
        Storage::disk('public')->delete($file->ruta);

        // Eliminar de la base de datos
        $file->delete();

        // 🔹 Refrescar la lista actual
        unset($this->cache[$this->currentFolderId]);

        $this->dispatch('toast', type: 'success', text: 'Archivo eliminado correctamente.');

        $this->cancelarEliminarArchivo();
    }

    /* Metodos para para mover contenido de carpetas */
    public function abrirMover($id, $tipo)
    {
        $this->movingId = $id;
        $this->movingType = $tipo;
        $this->destinationFolderId = null;
        $this->showMoveModal = true;
    }

    /* Mover archivos y carpetas */
    public function moverElemento()
    {
        if (!$this->movingId || !$this->movingType || $this->destinationFolderId === null) {
        $this->dispatch('toast', type: 'error', text: 'Selecciona una carpeta destino.');
        return;
        }

        try {
            // Normalizamos el destino (0 = raíz)
            $destino = $this->destinationFolderId == 0 ? 0 : $this->destinationFolderId;

            if ($this->movingType === 'folder') {
                $folder = Folder::find($this->movingId);
                if (!$folder) throw new \Exception('La carpeta no existe.');

                if ($folder->id === $destino) {
                    throw new \Exception('No puedes mover una carpeta dentro de sí misma.');
                }

                // Validar duplicado en destino
                $yaExiste = Folder::where('parent_id', $destino)
                    ->where('nombre', $folder->nombre)
                    ->exists();

                if ($yaExiste) {
                    throw new \Exception('Ya existe una carpeta con ese nombre en el destino.');
                }

                $folder->update(['parent_id' => $destino]);
            }

            if ($this->movingType === 'file') {
                $file = File::find($this->movingId);
                if (!$file) throw new \Exception('El archivo no existe.');

                if ($file->folder_id === $destino) {
                    throw new \Exception('El archivo ya está en esa carpeta.');
                }

                $file->update(['folder_id' => $destino]);
            }

            // Limpiar caché y refrescar vista
            unset($this->cache[$this->currentFolderId]);
            unset($this->cache[$this->destinationFolderId]);
            $this->dispatch('$refresh');

            // Resetear estado
            $this->showMoveModal = false;
            $this->movingId = null;
            $this->movingType = null;
            $this->destinationFolderId = null;

            $this->dispatch('toast', type: 'success', text: 'Elemento movido correctamente.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: $e->getMessage());
        }
    }

    private function obtenerCarpetasJerarquicas($parentId = 0, $prefix = '')
    {
        $carpetas = Folder::where('parent_id', $parentId)
            ->where('usuario_id', Auth::id())
            ->orderBy('nombre')
            ->get();

        $resultado = [];

        foreach ($carpetas as $carpeta) {
            $resultado[] = [
                'id' => $carpeta->id,
                'nombre' => $prefix . $carpeta->nombre,
            ];

            // Recursivo: añadir subcarpetas con más sangría
            $sub = $this->obtenerCarpetasJerarquicas($carpeta->id, $prefix . '— ');
            $resultado = array_merge($resultado, $sub);
        }

        return $resultado;
    }

    /* Funcion para poder obetener informacion de archvios caducados */
    public function scopeCaducados($query)
    {
        return $query->where('tiene_caducidad', 1)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '<', now());
    }

    public function scopePorVencer($query)
    {
        return $query->where('tiene_caducidad', 1)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '>=', now())
            ->whereDate('fecha_caducidad', '<=', now()->addDays(7));
    }




    public function render()
    {
        // Si está buscando, aplicar filtro
        if ($this->searching && trim($this->search) !== '') {
            $query = trim($this->search);

            $folders = Folder::where('parent_id', $this->currentFolderId)
                ->where('nombre', 'like', "%{$query}%")
                ->orderBy('nombre')
                ->get();

            $files = File::where('folder_id', $this->currentFolderId)
                ->where('nombre', 'like', "%{$query}%")
                ->orderBy('nombre')
                ->get();

            return view('livewire.drive.folder-manager', [
                'folders' => $folders,
                'files' => $files,
            ]);
        }

        // Si no está buscando o se limpió, usar caché normal
        if (!isset($this->cache[$this->currentFolderId])) {
            $this->cache[$this->currentFolderId] = [
                'folders' => Folder::where('parent_id', $this->currentFolderId)->orderBy('nombre')->get(),
                'files' => File::where('folder_id', $this->currentFolderId)->orderBy('nombre')->get(),
            ];
        }

        return view('livewire.drive.folder-manager', $this->cache[$this->currentFolderId]);
    }



}
