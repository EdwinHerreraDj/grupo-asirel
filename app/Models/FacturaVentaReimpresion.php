<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de auditoría de cada copia / reimpresión PDF generada a partir de
 * una factura ya emitida (o anulada). No es el documento fiscal ni el PDF
 * original: solo deja rastro de que se produjo una representación posterior.
 */
class FacturaVentaReimpresion extends Model
{
    protected $table = 'factura_venta_reimpresiones';

    public const TIPO_REIMPRESION = 'reimpresion';

    protected $fillable = [
        'factura_venta_id',
        'user_id',
        'tipo',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
