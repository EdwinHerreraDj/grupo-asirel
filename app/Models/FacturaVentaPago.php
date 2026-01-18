<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVentaPago extends Model
{
    use HasFactory;

    protected $table = 'factura_venta_pagos';

    protected $fillable = [
        'factura_venta_id',
        'fecha_pago',
        'importe',
        'metodo',
        'tipo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'importe'    => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(FacturaVenta::class);
    }
}
