<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CopiarDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $clipboard,
        public int $destinoFolderId,
        public int $userId
    ) {}

    public function handle(): void
    {
        foreach ($this->clipboard['items'] as $item) {
            [$tipo, $id] = explode(':', $item);

            if ($tipo === 'file') {
                $this->copiarArchivo((int) $id);
            }

            if ($tipo === 'folder') {
                $this->copiarCarpetaRecursiva((int) $id, $this->destinoFolderId);
            }
        }
    }

    /* ===============================
     * ARCHIVOS
     * =============================== */

    private function copiarArchivo(int $fileId): void
    {
        $file = File::find($fileId);
        if (!$file) return;

        $nombre = $this->resolverNombreDuplicado(
            File::where('folder_id', $this->destinoFolderId),
            $file->nombre
        );

        $nuevoPath = "drive/{$this->destinoFolderId}/{$nombre}";

        Storage::disk('local')->copy($file->ruta, $nuevoPath);

        File::create([
            'folder_id'  => $this->destinoFolderId,
            'usuario_id' => $this->userId,
            'nombre'     => $nombre,
            'ruta'       => $nuevoPath,
            'tipo'       => $file->tipo,
            'tamaño'     => $file->tamaño,
        ]);
    }

    /* ===============================
     * CARPETAS
     * =============================== */

    private function copiarCarpetaRecursiva(int $folderId, int $destinoPadre): void
    {
        $folder = Folder::find($folderId);
        if (!$folder) return;

        $nombre = $this->resolverNombreDuplicado(
            Folder::where('parent_id', $destinoPadre),
            $folder->nombre
        );

        $nueva = Folder::create([
            'nombre'     => $nombre,
            'parent_id'  => $destinoPadre,
            'usuario_id' => $this->userId,
            'tipo'       => 2,
        ]);

        Storage::disk('local')->makeDirectory("drive/{$nueva->id}");

        foreach ($folder->files as $file) {
            $this->copiarArchivoEnCarpeta($file, $nueva->id);
        }

        foreach ($folder->children as $child) {
            $this->copiarCarpetaRecursiva($child->id, $nueva->id);
        }
    }

    private function copiarArchivoEnCarpeta(File $file, int $destino): void
    {
        $nombre = $this->resolverNombreDuplicado(
            File::where('folder_id', $destino),
            $file->nombre
        );

        $nuevoPath = "drive/{$destino}/{$nombre}";

        Storage::disk('local')->copy($file->ruta, $nuevoPath);

        File::create([
            'folder_id'  => $destino,
            'usuario_id' => $this->userId,
            'nombre'     => $nombre,
            'ruta'       => $nuevoPath,
            'tipo'       => $file->tipo,
            'tamaño'     => $file->tamaño,
        ]);
    }

    /* ===============================
     * DUPLICADOS
     * =============================== */

    private function resolverNombreDuplicado($query, string $nombre): string
    {
        if (!$query->where('nombre', $nombre)->exists()) {
            return $nombre;
        }

        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $ext  = pathinfo($nombre, PATHINFO_EXTENSION);
        $i = 1;

        do {
            $nuevo = $ext
                ? "{$base} ({$i}).{$ext}"
                : "{$base} ({$i})";
            $i++;
        } while ($query->where('nombre', $nuevo)->exists());

        return $nuevo;
    }
}
