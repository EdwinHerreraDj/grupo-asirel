<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;

class EliminarDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tipo,
        public int $id,
        public int $userId
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            if ($this->tipo === 'file') {
                $this->eliminarArchivo($this->id);
            }

            if ($this->tipo === 'folder') {
                $this->eliminarCarpeta($this->id);
            }
        });
    }

    /* ===============================
     * ARCHIVO
     * =============================== */

    private function eliminarArchivo(int $fileId): void
    {
        $file = File::where('id', $fileId)
            ->where('usuario_id', $this->userId)
            ->first();

        if (!$file) return;

        $ruta = $file->ruta;

        $file->delete();

        // filesystem fuera de DB crítica
        Storage::disk('local')->delete($ruta);
    }

    /* ===============================
     * CARPETA
     * =============================== */

    private function eliminarCarpeta(int $folderId): void
    {
        $folder = Folder::where('id', $folderId)
            ->where('usuario_id', $this->userId)
            ->first();

        if (!$folder) return;

        $this->eliminarCarpetaRecursiva($folder);

        Storage::disk('local')->deleteDirectory("drive/{$folder->id}");
    }

    private function eliminarCarpetaRecursiva(Folder $folder): void
    {
        foreach ($folder->files as $file) {
            $this->eliminarArchivo($file->id);
        }

        foreach ($folder->children as $child) {
            $this->eliminarCarpetaRecursiva($child);
        }

        $folder->delete();
    }
}
