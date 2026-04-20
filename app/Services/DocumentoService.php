<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Obra;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentoService
{
    private const DISCO = 'public';
    private const CARPETA = 'documentos';
    private const MIME_PERMITIDO = 'application/pdf';
    private const TAMANO_MAX_BYTES = 20 * 1024 * 1024; // 20 MB

    public function subir(
        Obra $obra,
        DocumentoTipo $tipo,
        UploadedFile $archivo,
        ?Carbon $fechaVencimiento = null,
    ): Documento {
        $this->validarArchivo($archivo);

        $yaExiste = Documento::where('obra_id', $obra->id)
            ->where('documento_tipo_id', $tipo->id)
            ->exists();

        if ($yaExiste) {
            throw new RuntimeException('Ya existe un documento de este tipo. Usa la opción reemplazar.');
        }

        return DB::transaction(function () use ($obra, $tipo, $archivo, $fechaVencimiento) {
            $ruta = $this->guardarFichero($archivo);

            return Documento::create([
                'obra_id' => $obra->id,
                'documento_tipo_id' => $tipo->id,
                'tipo' => $tipo->nombre,
                'archivo' => $ruta,
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type' => $archivo->getMimeType(),
                'size' => $archivo->getSize(),
                'fecha_vencimiento' => $fechaVencimiento,
            ]);
        });
    }

    public function reemplazar(
        Documento $documento,
        UploadedFile $archivo,
        ?Carbon $fechaVencimiento = null,
    ): Documento {
        $this->validarArchivo($archivo);

        return DB::transaction(function () use ($documento, $archivo, $fechaVencimiento) {
            $rutaNueva = $this->guardarFichero($archivo);
            $rutaAntigua = $documento->archivo;

            $documento->update([
                'archivo' => $rutaNueva,
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type' => $archivo->getMimeType(),
                'size' => $archivo->getSize(),
                'fecha_vencimiento' => $fechaVencimiento,
            ]);

            if ($rutaAntigua && \Illuminate\Support\Facades\Storage::disk(self::DISCO)->exists($rutaAntigua)) {
                \Illuminate\Support\Facades\Storage::disk(self::DISCO)->delete($rutaAntigua);
            }

            return $documento->fresh();
        });
    }

    public function eliminar(Documento $documento): void
    {
        DB::transaction(function () use ($documento) {
            // El hook `deleting` del modelo se encarga de borrar el fichero f\u00edsico
            $documento->delete();
        });
    }

    private function validarArchivo(UploadedFile $archivo): void
    {
        if (! $archivo->isValid()) {
            throw new RuntimeException('El archivo no se ha subido correctamente.');
        }

        if ($archivo->getSize() > self::TAMANO_MAX_BYTES) {
            $mb = round(self::TAMANO_MAX_BYTES / 1024 / 1024);
            throw new RuntimeException("El archivo supera el tamaño máximo de {$mb} MB.");
        }

        if ($archivo->getMimeType() !== self::MIME_PERMITIDO) {
            throw new RuntimeException('Solo se permiten archivos PDF.');
        }
    }

    private function guardarFichero(UploadedFile $archivo): string
    {
        $nombreBase = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($nombreBase) ?: 'documento';
        $nombreUnico = $slug . '_' . time() . '_' . Str::random(6) . '.pdf';

        $ruta = $archivo->storeAs(self::CARPETA, $nombreUnico, self::DISCO);

        if (! $ruta) {
            throw new RuntimeException('No se pudo guardar el archivo en el almacenamiento.');
        }

        return $ruta;
    }
}
