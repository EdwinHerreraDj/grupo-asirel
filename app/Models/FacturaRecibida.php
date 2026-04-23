<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'importe', // legacy — espejo de base_imponible, se deprecar\u00e1
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
        'fecha_factura'        => 'date',
        'fecha_contable'       => 'date',
        'vencimiento'          => 'date',
        'base_imponible'       => 'float',
        'iva_porcentaje'       => 'float',
        'iva_importe'          => 'float',
        'retencion_porcentaje' => 'float',
        'retencion_importe'    => 'float',
        'total'                => 'float',
        'importe'              => 'float',
    ];

    // -------------------------
    // HOOKS
    // -------------------------

    /**
     * Recalcula autom\u00e1ticamente iva_importe, retencion_importe y total
     * a partir de base_imponible + porcentajes. Fuente \u00fanica de c\u00e1lculo.
     */
    protected static function booted(): void
    {
        static::saving(function (FacturaRecibida $factura) {
            $base = (float) ($factura->base_imponible ?? 0);
            $ivaPct = (float) ($factura->iva_porcentaje ?? 0);
            $retPct = (float) ($factura->retencion_porcentaje ?? 0);

            $ivaImporte = round($base * $ivaPct / 100, 2);
            $retImporte = round($base * $retPct / 100, 2);

            $factura->iva_importe       = $ivaImporte;
            $factura->retencion_importe = $retImporte;
            $factura->total             = round($base + $ivaImporte - $retImporte, 2);

            // Legacy: mantener importe sincronizado con base_imponible para
            // cualquier lectura que no haya migrado todav\u00eda.
            $factura->importe = round($base, 2);
        });
    }

    // -------------------------
    // RELACIONES
    // -------------------------

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function oficio(): BelongsTo
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'oficio_id');
    }

    // -------------------------
    // ESTADO
    // -------------------------

    public const ESTADOS = [
        'pendiente_emision_doc_pago' => ['label' => 'Pendiente emisión', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
        'pendiente_vencimiento'      => ['label' => 'Pendiente vencimiento', 'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
        'devuelta'                   => ['label' => 'Devuelta', 'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
        'pagada'                     => ['label' => 'Pagada', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'impagada'                   => ['label' => 'Impagada', 'color' => 'bg-red-100 text-red-700 border-red-200'],
    ];

    public const ESTADOS_CRITICOS = ['pagada', 'impagada'];

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado]['label'] ?? $this->estado;
    }

    public function estadoColor(): string
    {
        return self::ESTADOS[$this->estado]['color']
            ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function esEstadoCritico(string $nuevoEstado): bool
    {
        return in_array($nuevoEstado, self::ESTADOS_CRITICOS, true);
    }
}
