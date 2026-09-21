<?php

namespace App\Services\Rrhh;

use App\Models\Empleado;
use App\Models\File;
use App\Services\Drive\DriveStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Guarda un adjunto de RRHH (justificante, nómina, certificado, carta…) en
 * una subcarpeta protegida de la carpeta del empleado en el Drive.
 */
class AdjuntosRrhh
{
    public function __construct(
        private readonly CarpetasEmpleados $carpetas,
        private readonly DriveStorage $storage,
    ) {}

    /**
     * @param  string  $clave   una de CarpetasEmpleados::CARPETAS_INTERNAS
     * @param  string  $prefijo se antepone al nombre original (p. ej. "2026-09 Nómina")
     */
    public function guardar(Empleado $empleado, UploadedFile $archivo, string $clave, string $prefijo): File
    {
        $carpeta = $this->carpetas->carpetaInterna($empleado, $clave);
        $guardado = $this->storage->guardarSubida($archivo);

        try {
            return File::create([
                'folder_id' => $carpeta->id,
                'usuario_id' => auth()->id(),
                'nombre' => Str::limit(trim($prefijo).' - '.$archivo->getClientOriginalName(), 250, ''),
                'ruta' => $guardado['ruta'],
                'disco' => $guardado['disco'],
                'tipo' => $archivo->getMimeType(),
                'tamaño' => $archivo->getSize(),
                'tiene_caducidad' => false,
            ]);
        } catch (Throwable $e) {
            // Sin registro en BD no debe quedar el fichero huérfano.
            Storage::disk($guardado['disco'])->delete($guardado['ruta']);
            throw $e;
        }
    }
}
