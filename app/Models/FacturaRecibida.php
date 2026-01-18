<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacturaRecibida extends Model
{
    use HasFactory;

    protected $table = 'facturas_recibidas';

    protected $fillable = [
        'obra_id',
        'proveedor_id',
        'oficio_id',
        'tipo_coste',
        'numero_factura',
        'concepto',
        'importe', 
        'base_imponible',
        'iva_porcentaje',
        'iva_importe',
        'retencion_porcentaje',
        'retencion_importe',
        'total',
        'fecha_factura',
        'fecha_contable',
        'vencimiento',
        'tipo_pago',
        'estado',
        'adjunto',
    ];


    protected $casts = [
        'fecha_factura' => 'date',
        'fecha_contable' => 'date',
        'vencimiento' => 'date',
    ];

    // RELACIONES
    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function oficio()
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'oficio_id');
    }
}
