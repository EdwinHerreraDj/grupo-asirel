<?php

namespace App\Services\facturas;

use App\Models\FacturaVenta;
use App\Models\FacturaVentaDocumento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Gestión de la documentación adjunta (soporte documental) de una factura de
 * venta. Independiente del documento fiscal: subir o borrar un adjunto NUNCA
 * toca importes, fechas, numeración, estado ni el PDF original emitido.
 */
class FacturaVentaDocumentoService
{
    private const DISCO = 'public';
    private const CARPETA = 'facturas/venta/documentos';

    public function subir(FacturaVenta $factura, UploadedFile $archivo): FacturaVentaDocumento
    {
        $ruta = $archivo->store(self::CARPETA, self::DISCO);

        if (! $ruta) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }

        try {
            return FacturaVentaDocumento::create([
                'factura_venta_id' => $factura->id,
                'user_id'          => Auth::id(),
                'nombre_original'  => $archivo->getClientOriginalName(),
                'ruta'             => $ruta,
                'mime'             => $archivo->getClientMimeType(),
                'tamano'           => $archivo->getSize(),
            ]);
        } catch (\Throwable $e) {
            // Si falla el registro, no dejamos el archivo huérfano en disco.
            if (Storage::disk(self::DISCO)->exists($ruta)) {
                Storage::disk(self::DISCO)->delete($ruta);
            }
            throw $e;
        }
    }

    public function eliminar(FacturaVentaDocumento $documento): void
    {
        // El hook deleting del modelo borra el archivo físico.
        DB::transaction(fn () => $documento->delete());
    }
}
