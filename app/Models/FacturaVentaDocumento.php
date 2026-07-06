<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Documento adjunto (soporte documental) de una factura de venta.
 * No es el documento fiscal ni el PDF original: solo documentación asociada.
 */
class FacturaVentaDocumento extends Model
{
    protected $table = 'factura_venta_documentos';

    protected $fillable = [
        'factura_venta_id',
        'user_id',
        'nombre_original',
        'ruta',
        'mime',
        'tamano',
    ];

    protected $casts = [
        'tamano' => 'integer',
    ];

    protected static function booted(): void
    {
        // Al borrar el registro, elimina el archivo físico del disco.
        static::deleting(function (FacturaVentaDocumento $doc) {
            if ($doc->ruta && Storage::disk('public')->exists($doc->ruta)) {
                Storage::disk('public')->delete($doc->ruta);
            }
        });
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function esImagen(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->nombre_original, PATHINFO_EXTENSION) ?: 'FILE');
    }

    public function tamanoLegible(): string
    {
        $bytes = (int) $this->tamano;
        if ($bytes <= 0) {
            return '—';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($unidades) - 1);

        return round($bytes / (1024 ** $i), $i === 0 ? 0 : 1) . ' ' . $unidades[$i];
    }
}
