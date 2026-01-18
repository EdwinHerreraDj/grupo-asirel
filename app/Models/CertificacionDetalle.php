<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificacionDetalle extends Model
{
    protected $table = 'certificacion_detalles';

    protected $fillable = [
        'certificacion_id',
        'concepto',
        'unidad',
        'cantidad',
        'precio_unitario',
        'importe_linea',
    ];

    public function certificacion()
    {
        return $this->belongsTo(Certificacion::class);
    }
}
