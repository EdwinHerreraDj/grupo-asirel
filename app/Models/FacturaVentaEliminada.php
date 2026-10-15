<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rastro de una factura de venta que se eliminó. */
class FacturaVentaEliminada extends Model
{
    protected $table = 'facturas_venta_eliminadas';

    protected $fillable = [
        'factura_id', 'serie', 'numero_factura', 'numero_formateado', 'estado', 'origen', 'fecha_emision',
        'cliente_id', 'cliente_nombre', 'obra_id', 'obra_nombre', 'codigo_certificacion',
        'base_imponible', 'iva_importe', 'retencion_importe', 'total',
        'usuario_id', 'usuario_nombre', 'ip', 'motivo', 'numero_liberado', 'datos',
    ];

    protected $casts = [
        'fecha_emision' => 'date:Y-m-d',
        'base_imponible' => 'decimal:2',
        'iva_importe' => 'decimal:2',
        'retencion_importe' => 'decimal:2',
        'total' => 'decimal:2',
        'numero_liberado' => 'boolean',
        'datos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
