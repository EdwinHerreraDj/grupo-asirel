<?php

namespace App\Services;

use App\Models\FacturaRecibida;
use App\Models\Obra;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Operaciones CRUD sobre facturas recibidas.
 *
 * Garantiza:
 *   - Mutaci\u00f3n + subida/sustituci\u00f3n de adjunto en la misma transacci\u00f3n.
 *   - Si algo falla antes del commit, se borra el adjunto reci\u00e9n subido
 *     para evitar hu\u00e9rfanos en disco.
 *   - C\u00e1lculos fiscales resueltos por el hook `saving` del modelo
 *     (fuente \u00fanica de verdad).
 */
class FacturaRecibidaService
{
    private const DISCO = 'public';
    private const CARPETA = 'facturas/recibidas';

    public function crear(Obra $obra, array $data, ?UploadedFile $adjunto = null): FacturaRecibida
    {
        $rutaAdjunto = null;

        return DB::transaction(function () use ($obra, $data, $adjunto, &$rutaAdjunto) {
            if ($adjunto) {
                $rutaAdjunto = $adjunto->store(self::CARPETA, self::DISCO);
            }

            try {
                return FacturaRecibida::create([
                    ...$data,
                    'obra_id'  => $obra->id,
                    'adjunto'  => $rutaAdjunto,
                ]);
            } catch (\Throwable $e) {
                if ($rutaAdjunto) {
                    Storage::disk(self::DISCO)->delete($rutaAdjunto);
                }
                throw $e;
            }
        });
    }

    public function actualizar(
        FacturaRecibida $factura,
        array $data,
        ?UploadedFile $adjunto = null,
    ): FacturaRecibida {
        $adjuntoAnterior = $factura->adjunto;
        $rutaAdjunto = null;

        DB::transaction(function () use ($factura, $data, $adjunto, $adjuntoAnterior, &$rutaAdjunto) {
            if ($adjunto) {
                $rutaAdjunto = $adjunto->store(self::CARPETA, self::DISCO);
                $data['adjunto'] = $rutaAdjunto;
            }

            try {
                $factura->update($data);
            } catch (\Throwable $e) {
                if ($rutaAdjunto) {
                    Storage::disk(self::DISCO)->delete($rutaAdjunto);
                }
                throw $e;
            }
        });

        // Si la actualizaci\u00f3n reemplaz\u00f3 el adjunto, borramos el antiguo
        // fuera de la transacci\u00f3n (ya est\u00e1 commiteada).
        if ($rutaAdjunto && $adjuntoAnterior && $adjuntoAnterior !== $rutaAdjunto) {
            Storage::disk(self::DISCO)->delete($adjuntoAnterior);
        }

        return $factura->fresh();
    }

    public function eliminar(FacturaRecibida $factura): void
    {
        $adjunto = $factura->adjunto;

        DB::transaction(function () use ($factura) {
            $factura->delete();
        });

        if ($adjunto && Storage::disk(self::DISCO)->exists($adjunto)) {
            Storage::disk(self::DISCO)->delete($adjunto);
        }
    }

    public function cambiarEstado(FacturaRecibida $factura, string $nuevoEstado): FacturaRecibida
    {
        if (! array_key_exists($nuevoEstado, FacturaRecibida::ESTADOS)) {
            throw new \InvalidArgumentException("Estado inválido: {$nuevoEstado}");
        }

        $factura->update(['estado' => $nuevoEstado]);

        return $factura->fresh();
    }

    public function cambiarTipoPago(FacturaRecibida $factura, ?string $tipoPago): FacturaRecibida
    {
        $factura->update(['tipo_pago' => $tipoPago]);

        return $factura->fresh();
    }
}
