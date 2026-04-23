<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    protected $table = 'certificaciones';

    protected $fillable = [
        'obra_id',
        'cliente_id',
        'obra_gasto_categoria_id',
        'numero_certificacion',
        'fecha_ingreso',
        'fecha_contable',
        'fecha_vencimiento',
        'tipo_documento',
        'estado_certificacion',
        'estado_factura',
        'iva_porcentaje',
        'retencion_porcentaje',
        'base_imponible',
        'iva_importe',
        'retencion_importe',
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

        'fecha_ingreso'  => 'date',
        'fecha_contable' => 'date',
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

    public function eventos()
    {
        return $this->hasMany(CertificacionEvento::class)
            ->orderBy('created_at');
    }

    /* =========================
     * GUARDIAS DE ESTADO
     * ========================= */

    public function estaPendiente(): bool
    {
        return $this->estado_certificacion === 'pendiente';
    }

    public function estaAceptada(): bool
    {
        return $this->estado_certificacion === 'aceptada';
    }

    public function estaFacturada(): bool
    {
        return $this->estado_factura === 'facturada';
    }

    public function puedeEditar(): bool
    {
        return $this->estaPendiente();
    }

    public function puedeAceptar(): bool
    {
        return $this->estaPendiente() && $this->detalles()->exists();
    }

    public function puedeEliminar(): bool
    {
        return ! $this->estaFacturada();
    }

    public function puedeAnular(): bool
    {
        return $this->estaAceptada() && ! $this->estaFacturada();
    }
}
