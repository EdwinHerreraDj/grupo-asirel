<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    protected $table = 'certificaciones';

    protected $fillable = [
        'obra_id',
        'fecha_ingreso',
        'fecha_contable',
        'cliente_id',
        'numero_certificacion',
        'obra_gasto_categoria_id',
        'estado_certificacion',
        'estado_factura',
        'iva_porcentaje',
        'retencion_porcentaje',
        'base_imponible',
        'iva_importe',
        'retencion_importe',
        'tipo_documento',
        'fecha_vencimiento',
        'estado_certificacion',
        'estado_factura',
        'total',
        'adjunto_url',
    ];

    protected $casts = [
        'base_imponible'       => 'float',
        'iva_porcentaje'       => 'float',
        'iva_importe'          => 'float',
        'retencion_porcentaje' => 'float',
        'retencion_importe'    => 'float',
        'total'                => 'float',

        'fecha_ingreso'   => 'date',
        'fecha_contable'  => 'date',
    ];


    /* =========================
     * RELACIONES
     * ========================= */

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function oficio()
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'obra_gasto_categoria_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(
            CertificacionDetalle::class,
            'certificacion_id'
        );
    }

    public function facturasVenta()
    {
        return $this->belongsToMany(
            FacturaVenta::class,
            'factura_venta_certificacion'
        );
    }
}
