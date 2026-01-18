<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacturaVenta extends Model
{
    use HasFactory;

    protected $table = 'facturas_venta';

    protected $fillable = [
        'serie',
        'origen',
        'numero',
        'numero_factura',

        'fecha_emision',
        'fecha_contable',
        'vencimiento',

        'obra_id',
        'cliente_id',
        'codigo_certificacion',

        'base_imponible',
        'iva_porcentaje',
        'iva_importe',
        'retencion_porcentaje',
        'retencion_importe',
        'total',

        'estado',
        'pdf_url',
        'observaciones',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_emision'   => 'date',
        'fecha_contable'  => 'date',
        'vencimiento'     => 'date',

        'base_imponible'       => 'float',
        'iva_porcentaje'       => 'float',
        'iva_importe'          => 'float',
        'retencion_porcentaje' => 'float',
        'retencion_importe'    => 'float',
        'total'                => 'float',
    ];

    /* =========================
     * RELACIONES
     * ========================= */

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(
            FacturaVentaDetalle::class,
            'factura_venta_id'
        );
    }
    public function pagos()
    {
        return $this->hasMany(FacturaVentaPago::class);
    }
    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('importe');
    }

    public function pendientePago(): float
    {
        return round($this->total - $this->totalPagado(), 2);
    }
    public function puedeMarcarPagada(): bool
    {
        return in_array($this->estado, ['emitida', 'enviada'])
            && $this->pendientePago() <= 0;
    }

    public function recalcularEstadoPorPagos(): void
    {
        if ($this->puedeMarcarPagada()) {
            $this->update(['estado' => 'pagada']);
        }
    }

    public function puedeRegistrarPago(): bool
    {
        return in_array($this->estado, ['emitida', 'enviada'])
            && $this->pendientePago() > 0;
    }

    public function puedeEmitirse(): bool
    {
        return $this->estado === 'borrador'
            && $this->detalles()->count() > 0;
    }



    public function puedeAnular(): bool
    {
        return in_array($this->estado, ['emitida', 'enviada'])
            && $this->totalPagado() == 0;
    }

    public function anular(string $motivo): void
    {
        if (! $this->puedeAnular()) {
            return;
        }

        $this->update([
            'estado' => 'anulada',
            'motivo_anulacion' => $motivo,
        ]);
    }

    public function certificaciones()
    {
        return $this->hasMany(
            Certificacion::class,
            'numero_certificacion',
            'codigo_certificacion'
        )->where('estado_factura', 'facturada');
    }
}
