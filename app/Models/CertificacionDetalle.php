<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificacionDetalle extends Model
{
    protected $table = 'certificacion_detalles';

    protected $fillable = [
        'certificacion_id',
        'presupuesto_venta_partida_id',
        'concepto',
        'unidad',
        'cantidad',
        'precio_unitario',
        'importe_linea',
        'comentario',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'precio_unitario' => 'float',
        'importe_linea'   => 'float',
    ];

    public function certificacion(): BelongsTo
    {
        return $this->belongsTo(Certificacion::class);
    }

    public function partidaVenta(): BelongsTo
    {
        return $this->belongsTo(
            PresupuestoVentaPartida::class,
            'presupuesto_venta_partida_id'
        );
    }
}
