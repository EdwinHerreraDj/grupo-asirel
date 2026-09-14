<?php

namespace App\Services\Drive;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Almacenamiento físico de los archivos del Drive.
 *
 * Conviven dos formatos:
 *  - `uploads/...` en el disco público (subidas antiguas de la app React),
 *  - `drive/...` en el disco privado `local` (formato antiguo y subidas nuevas).
 *
 * Todo lo nuevo se guarda en el disco privado (no accesible por URL) y solo se
 * sirve a través de los controladores, con sesión.
 */
class DriveStorage
{
    public const DISCO_PRIVADO = 'local';

    public const CARPETA_PRIVADA = 'drive/archivos';

    /** Extensiones que se muestran en el navegador; el resto se descarga. */
    private const VISTA_EN_LINEA = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Disco donde está el fichero. Usa `files.disco` y, si no está ahí
     * (datos antiguos o incoherentes), prueba el otro disco.
     */
    public function disco(File $file): string
    {
        $preferido = $file->disco
            ?: (str_starts_with((string) $file->ruta, 'uploads/') ? 'public' : self::DISCO_PRIVADO);

        if (Storage::disk($preferido)->exists($file->ruta)) {
            return $preferido;
        }

        $alternativo = $preferido === 'public' ? self::DISCO_PRIVADO : 'public';

        return Storage::disk($alternativo)->exists($file->ruta) ? $alternativo : $preferido;
    }

    public function existe(File $file): bool
    {
        return Storage::disk($this->disco($file))->exists($file->ruta);
    }

    public function rutaAbsoluta(File $file): string
    {
        return Storage::disk($this->disco($file))->path($file->ruta);
    }

    /**
     * Guarda una subida en el disco privado.
     *
     * @return array{ruta: string, disco: string}
     */
    public function guardarSubida(UploadedFile $archivo): array
    {
        $ruta = $archivo->storeAs(
            $this->carpetaDelMes(),
            $this->nombreFisico($archivo->getClientOriginalExtension()),
            self::DISCO_PRIVADO,
        );

        if (! $ruta) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }

        return ['ruta' => $ruta, 'disco' => self::DISCO_PRIVADO];
    }

    /**
     * Copia un fichero local (p. ej. extraído de un ZIP o del disco público)
     * al disco privado.
     *
     * @return array{ruta: string, disco: string}
     */
    public function guardarDesdeRuta(string $origenAbsoluto, string $nombreOriginal): array
    {
        $ruta = $this->carpetaDelMes().'/'.$this->nombreFisico(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        $stream = fopen($origenAbsoluto, 'rb');
        if ($stream === false) {
            throw new RuntimeException('No se pudo leer el archivo de origen.');
        }

        try {
            if (! Storage::disk(self::DISCO_PRIVADO)->put($ruta, $stream)) {
                throw new RuntimeException('No se pudo guardar el archivo.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return ['ruta' => $ruta, 'disco' => self::DISCO_PRIVADO];
    }

    public function borrar(File $file): void
    {
        $disco = $this->disco($file);

        if (Storage::disk($disco)->exists($file->ruta)) {
            Storage::disk($disco)->delete($file->ruta);
        }
    }

    public function descargar(File $file)
    {
        return Storage::disk($this->disco($file))->download($file->ruta, $file->nombre);
    }

    /** PDF e imágenes en el navegador; el resto como descarga. */
    public function ver(File $file)
    {
        $extension = strtolower(pathinfo($file->nombre, PATHINFO_EXTENSION));

        if (! in_array($extension, self::VISTA_EN_LINEA, true)) {
            return $this->descargar($file);
        }

        return Storage::disk($this->disco($file))->response($file->ruta, $file->nombre, [], 'inline');
    }

    private function carpetaDelMes(): string
    {
        return self::CARPETA_PRIVADA.'/'.now()->format('Y/m');
    }

    private function nombreFisico(?string $extension): string
    {
        $extension = strtolower(preg_replace('/[^A-Za-z0-9]/', '', (string) $extension));

        return Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
    }
}
