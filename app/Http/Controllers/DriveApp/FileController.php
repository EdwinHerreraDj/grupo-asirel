<?php

namespace App\Http\Controllers\DriveApp;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\Drive\DriveStorage;

class FileController extends Controller
{
    /**
     * Vista previa de un archivo del Drive (PDF e imágenes en el navegador;
     * el resto se descarga). Sirve ambos formatos de almacenamiento.
     */
    public function ver(File $file, DriveStorage $storage)
    {
        if (! $storage->existe($file)) {
            abort(404);
        }

        return $storage->ver($file);
    }
}
