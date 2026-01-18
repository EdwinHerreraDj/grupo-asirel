<?php

namespace App\Http\Controllers\DriveApp;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use ZipArchive;


class FileController extends Controller
{
    public function descargar(File $file)
    {
        $path = $file->ruta;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'El archivo no se encuentra en el servidor.');
        }

        return Storage::disk('local')->download($path, $file->nombre);
    }


    public function descargarCarpeta($id)
    {
        $folder = Folder::findOrFail($id);

        // Crear un nombre legible para el zip
        $zipFileName = str_replace(' ', '_', $folder->nombre) . '.zip';
        $zipPath = storage_path("app/temp/{$zipFileName}");

        // Asegurar el directorio temporal
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Crear el archivo ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->agregarCarpetaAlZip($zip, $folder);
            $zip->close();
        }

        // Devolver descarga al navegador
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Agregar carpeta y su contenido recursivamente al ZIP
     */
    private function agregarCarpetaAlZip(ZipArchive $zip, Folder $folder, $path = '')
    {
        // Agregar carpeta al zip (vacía al principio)
        $folderPath = $path . $folder->nombre . '/';
        $zip->addEmptyDir($folderPath);

        // 🔹 Agregar archivos de la carpeta actual
        $files = File::where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            if (Storage::disk('local')->exists($file->ruta)) {
                $filePath = Storage::disk('local')->path($file->ruta);
                $zip->addFile($filePath, $folderPath . $file->nombre);
            }
        }

        // 🔹 Agregar subcarpetas recursivamente
        $subfolders = Folder::where('parent_id', $folder->id)->get();
        foreach ($subfolders as $subfolder) {
            $this->agregarCarpetaAlZip($zip, $subfolder, $folderPath);
        }
    }
    
    public function ver(File $file)
    {
        // Seguridad mínima (ajusta si hay roles)
        if ($file->usuario_id !== auth()->id()) {
            abort(403);
        }

        // Ruta real
        $path = Storage::disk('local')->path($file->ruta);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
