<?php

namespace App\Livewire\Drive;

use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Symfony\Component\Finder\Finder;
use Illuminate\Http\File as HttpFile;


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

    /* Manejo de pronto a vencer archivos */
    public bool $showCaducidades = false;
    public string $filtroCaducidad = 'all';

    protected array $folderPathCache = [];
    protected array $folderMap = [];

    /* Variables para cambiar nombre a los archivos */
    public ?int $renamingFolderId = null;
    public string $nombreEditadoFolder = '';

    public ?int $renamingFileId = null;
    public string $nombreEditadoFile = '';





    public function mount()
    {
        $this->setCurrentFolder(0);
    }

    public function buscar()
    {
        $this->searching = true;
    }

    public function abrirCaducidades(): void
    {
        $this->showCaducidades = true;
    }

    public function cerrarCaducidades(): void
    {
        $this->showCaducidades = false;
        $this->filtroCaducidad = 'all';
    }

    public function getDocumentosCaducidadProperty()
    {
        $from = now()->startOfDay();
        $query = File::query()
            ->where('usuario_id', Auth::id())
            ->where('tiene_caducidad', 1)
            ->whereNotNull('fecha_caducidad')
            ->with('folder');

        switch ($this->filtroCaducidad) {
            case 'expired':
                $query->whereDate('fecha_caducidad', '<', $from);
                break;

            case '2weeks':
                $query->whereBetween('fecha_caducidad', [$from, now()->addWeeks(2)->endOfDay()]);
                break;

            case '2months':
                $query->whereBetween('fecha_caducidad', [$from, now()->addMonths(2)->endOfDay()]);
                break;

            case '4months':
                $query->whereBetween('fecha_caducidad', [$from, now()->addMonths(4)->endOfDay()]);
                break;

            case '6months':
                $query->whereBetween('fecha_caducidad', [$from, now()->addMonths(6)->endOfDay()]);
                break;

            case 'all':
            default:
                // sin filtro extra
                break;
        }

        return $query->orderBy('fecha_caducidad')->get();
    }

    private function buildFolderMap(): void
    {
        if (!empty($this->folderMap)) return;

        $folders = Folder::where('usuario_id', Auth::id())
            ->get(['id', 'nombre', 'parent_id']);

        foreach ($folders as $f) {
            $this->folderMap[$f->id] = [
                'nombre' => $f->nombre,
                'parent_id' => $f->parent_id,
            ];
        }
    }

    public function rutaDeCarpeta(?int $folderId): string
    {
        if (!$folderId) return 'Raíz';

        $this->buildFolderMap();

        if (isset($this->folderPathCache[$folderId])) {
            return $this->folderPathCache[$folderId];
        }

        $parts = [];
        $current = $folderId;

        while ($current && isset($this->folderMap[$current])) {
            array_unshift($parts, $this->folderMap[$current]['nombre']);
            $current = $this->folderMap[$current]['parent_id'];
        }

        return $this->folderPathCache[$folderId] = 'Raíz / ' . implode(' / ', $parts);
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

    private function eliminarCarpetaRecursiva(Folder $folder): void
    {
        // 1. Eliminar subcarpetas primero
        foreach ($folder->children as $child) {
            $this->eliminarCarpetaRecursiva($child);
        }

        // 2. Eliminar archivos de la carpeta
        $files = File::where('folder_id', $folder->id)->get();

        foreach ($files as $file) {
            if (Storage::disk('local')->exists($file->ruta)) {
                Storage::disk('local')->delete($file->ruta);
            }
            $file->delete();
        }

        // 3. Eliminar carpeta física
        Storage::disk('local')->deleteDirectory("drive/{$folder->id}");

        // 4. Eliminar carpeta en BD
        $folder->delete();
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
        if (! $this->deletingId) return;

        $folder = Folder::with('children')->find($this->deletingId);

        if (! $folder) {
            $this->dispatch('toast', type: 'error', text: 'La carpeta ya no existe.');
            $this->cancelarEliminar();
            return;
        }

        $this->eliminarCarpetaRecursiva($folder);

        // Limpiar estado y refrescar
        $this->cancelarEliminar();
        $this->cache = [];

        $this->dispatch('$refresh');
        $this->dispatch('toast', type: 'success', text: 'Carpeta eliminada con todo su contenido.');
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



    public function descomprimirZip(int $fileId): void
    {
        $zipDb = File::findOrFail($fileId);

        // 1) Validar ZIP
        if (strtolower(pathinfo($zipDb->nombre, PATHINFO_EXTENSION)) !== 'zip') {
            $this->dispatch('toast', type: 'error', text: 'El archivo no es un ZIP.');
            return;
        }

        $zipPath = Storage::disk('local')->path($zipDb->ruta);

        if (!is_file($zipPath)) {
            $this->dispatch('toast', type: 'error', text: 'El archivo físico no existe.');
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->dispatch('toast', type: 'error', text: 'No se pudo abrir el ZIP.');
            return;
        }

        // 2) Extraer a tmp
        $tmpPath = storage_path('app/tmp/unzip_' . uniqid());
        if (!is_dir($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }

        $zip->extractTo($tmpPath);
        $zip->close();

        $baseFolderId = $zipDb->folder_id ?? 0;

        // Mapa para convertir "ruta/carpeta" => folder_id
        $folderMap = [];

        /*
    |--------------------------------------------------------------------------
    | 3) Crear TODAS las carpetas (aunque estén vacías)
    |--------------------------------------------------------------------------
    */
        $dirFinder = new Finder();
        $dirFinder->directories()->in($tmpPath)->ignoreDotFiles(true);

        foreach ($dirFinder as $dir) {
            // ruta relativa dentro del zip extraído
            $relative = str_replace('\\', '/', $dir->getRelativePathname());
            if ($relative === '') continue;

            $parts = explode('/', $relative);
            $parentId = $baseFolderId;
            $pathKey = '';

            foreach ($parts as $folderName) {
                if ($folderName === '') continue;

                $pathKey .= '/' . $folderName;

                if (!isset($folderMap[$pathKey])) {
                    $folder = Folder::firstOrCreate([
                        'nombre'     => $folderName,
                        'parent_id'  => $parentId,
                        'usuario_id' => Auth::id(),
                    ], [
                        'tipo' => $parentId === 0 ? 1 : 2,
                    ]);

                    Storage::disk('local')->makeDirectory("drive/{$folder->id}");
                    $folderMap[$pathKey] = $folder->id;
                }

                $parentId = $folderMap[$pathKey];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 4) Procesar archivos (si existen)
    |--------------------------------------------------------------------------
    */
        $fileFinder = new Finder();
        $fileFinder->files()->in($tmpPath)->ignoreDotFiles(true);

        $procesados = 0;

        foreach ($fileFinder as $f) {
            $relative = str_replace('\\', '/', $f->getRelativePathname());
            $parts = explode('/', $relative);

            $parentId = $baseFolderId;
            $pathKey = '';

            // resolver el folder_id destino según el path
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $pathKey .= '/' . $parts[$i];
                if (isset($folderMap[$pathKey])) {
                    $parentId = $folderMap[$pathKey];
                }
            }

            $fileName = end($parts);

            // evitar duplicados
            if (File::where('folder_id', $parentId)->where('nombre', $fileName)->exists()) {
                $base = pathinfo($fileName, PATHINFO_FILENAME);
                $ext  = pathinfo($fileName, PATHINFO_EXTENSION);
                $n = 1;

                do {
                    $fileName = $ext ? "{$base}_{$n}.{$ext}" : "{$base}_{$n}";
                    $n++;
                } while (File::where('folder_id', $parentId)->where('nombre', $fileName)->exists());
            }

            $storedPath = Storage::disk('local')->putFileAs(
                "drive/{$parentId}",
                new HttpFile($f->getRealPath()),
                $fileName
            );

            File::create([
                'folder_id'  => $parentId,
                'usuario_id' => Auth::id(),
                'nombre'     => $fileName,
                'ruta'       => $storedPath,
                'tipo'       => pathinfo($fileName, PATHINFO_EXTENSION) ?: ($f->getExtension() ?: 'file'),
                'tamaño'     => $f->getSize(),
            ]);

            $procesados++;
        }

        // 5) Limpiar tmp
        \Illuminate\Support\Facades\File::deleteDirectory($tmpPath);

        // 6) Refrescar
        $this->cache = [];
        unset($this->cache[$this->currentFolderId]);
        $this->dispatch('$refresh');

        $this->dispatch(
            'toast',
            type: 'success',
            text: "ZIP descomprimido. Carpetas creadas y {$procesados} archivo(s) importado(s)."
        );
    }

    public function scopePorVencer($query)
    {
        return $query->where('tiene_caducidad', 1)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '>=', now())
            ->whereDate('fecha_caducidad', '<=', now()->addDays(7));
    }


    /* METODOS PARA RENOMBRAR ARCHIVOS */
    public function iniciarRenombrarArchivo(int $fileId): void
    {
        $file = \App\Models\File::findOrFail($fileId);

        $this->renamingFileId = $file->id;
        $this->nombreEditadoFile = pathinfo($file->nombre, PATHINFO_FILENAME);
    }

    public function cancelarRenombrarArchivo(): void
    {
        $this->renamingFileId = null;
        $this->nombreEditadoFile = '';
    }

    public function guardarRenombrarArchivo(): void
    {
        if (!$this->renamingFileId) return;

        $file = File::findOrFail($this->renamingFileId);

        $base = trim($this->nombreEditadoFile);

        if ($base === '') {
            $this->addError('nombreEditadoFile', 'El nombre no puede estar vacío.');
            return;
        }

        // Mantener extensión original
        $ext = strtolower(pathinfo($file->nombre, PATHINFO_EXTENSION));
        $nuevoNombre = $base . ($ext ? '.' . $ext : '');

        // SOLO actualizar nombre (NO ruta)
        $file->update([
            'nombre' => $nuevoNombre,
        ]);

        $this->renamingFileId = null;
        $this->nombreEditadoFile = '';

        $this->dispatch('toast', type: 'success', text: 'Archivo renombrado correctamente');
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
