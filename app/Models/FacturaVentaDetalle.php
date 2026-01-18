<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacturaVentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'factura_venta_detalles';

    protected $fillable = [
        'factura_venta_id',
        'certificacion_id',
        'certificacion_detalle_id',

        'concepto',
        'unidad',
        'cantidad',
        'precio_unitario',
        'importe_linea',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'precio_unitario' => 'float',
        'importe_linea'   => 'float',
    ];

    /* =========================
     * RELACIONES
     * ========================= */

    public function factura()
    {
        return $this->belongsTo(FacturaVenta::class, 'factura_venta_id');
    }

    public function certificacion()
    {
        return $this->belongsTo(Certificacion::class);
    }

    public function certificacionDetalle()
    {
        return $this->belongsTo(CertificacionDetalle::class);
    }

    
}
