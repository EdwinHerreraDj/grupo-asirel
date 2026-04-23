<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class FacturaVenta extends Model
{
    use HasFactory;

    protected $table = 'facturas_venta';

    /* =========================
     * ESTADOS
     * ========================= */

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_EMITIDA  = 'emitida';
    public const ESTADO_ENVIADA  = 'enviada';
    public const ESTADO_PAGADA   = 'pagada';
    public const ESTADO_ANULADA  = 'anulada';

    public const ESTADOS_EDITABLES = [self::ESTADO_BORRADOR];
    public const ESTADOS_COBRABLES = [self::ESTADO_EMITIDA, self::ESTADO_ENVIADA];

    public const ESTADOS_META = [
        self::ESTADO_BORRADOR => ['label' => 'Borrador', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
        self::ESTADO_EMITIDA  => ['label' => 'Emitida',  'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
        self::ESTADO_ENVIADA  => ['label' => 'Enviada',  'color' => 'bg-cyan-100 text-cyan-700 border-cyan-200'],
        self::ESTADO_PAGADA   => ['label' => 'Pagada',   'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        self::ESTADO_ANULADA  => ['label' => 'Anulada',  'color' => 'bg-red-100 text-red-700 border-red-200'],
    ];

    protected $fillable = [
        'serie',
        'origen',
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
        'adjunto',
        'observaciones',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_emision'  => 'date',
        'fecha_contable' => 'date',
        'vencimiento'    => 'date',

        'base_imponible'       => 'float',
        'iva_porcentaje'       => 'float',
        'iva_importe'          => 'float',
        'retencion_porcentaje' => 'float',
        'retencion_importe'    => 'float',
        'total'                => 'float',
    ];

    /* =========================
     * HOOKS
     * ========================= */

    protected static function booted(): void
    {
        static::deleting(function (FacturaVenta $factura) {
            // Borra pdf generado y adjunto de la factura al eliminar el registro.
            foreach (['pdf_url', 'adjunto'] as $campo) {
                if ($factura->{$campo} && Storage::disk('public')->exists($factura->{$campo})) {
                    Storage::disk('public')->delete($factura->{$campo});
                }
            }
        });
    }

    /* =========================
     * RELACIONES
     * ========================= */

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaVentaDetalle::class, 'factura_venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(FacturaVentaPago::class);
    }

    public function certificaciones(): HasMany
    {
        return $this->hasMany(
            Certificacion::class,
            'numero_certificacion',
            'codigo_certificacion'
        )->where('estado_factura', 'facturada');
    }

    public function certificacionesConImportes(): BelongsToMany
    {
        return $this->belongsToMany(
            Certificacion::class,
            'factura_venta_certificacion',
            'factura_venta_id',
            'certificacion_id',
        )->withPivot(['base_imponible', 'iva_importe', 'retencion_importe', 'total']);
    }

    /* =========================
     * HELPERS DE IMPORTE
     * ========================= */

    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('importe');
    }

    public function pendientePago(): float
    {
        return round($this->total - $this->totalPagado(), 2);
    }

    /* =========================
     * GUARDIAS DE ESTADO
     * ========================= */

    public function esEditable(): bool
    {
        return in_array($this->estado, self::ESTADOS_EDITABLES, true);
    }

    public function puedeMarcarPagada(): bool
    {
        return in_array($this->estado, self::ESTADOS_COBRABLES, true)
            && $this->pendientePago() <= 0;
    }

    public function puedeRegistrarPago(): bool
    {
        return in_array($this->estado, self::ESTADOS_COBRABLES, true)
            && $this->pendientePago() > 0;
    }

    public function puedeEmitirse(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR
            && $this->detalles()->count() > 0;
    }

    public function puedeAnular(): bool
    {
        return in_array($this->estado, self::ESTADOS_COBRABLES, true)
            && $this->totalPagado() == 0;
    }

    public function recalcularEstadoPorPagos(): void
    {
        if ($this->puedeMarcarPagada()) {
            $this->update(['estado' => self::ESTADO_PAGADA]);
        }
    }

    /* =========================
     * PRESENTACIÓN
     * ========================= */

    public function estadoMeta(): array
    {
        return self::ESTADOS_META[$this->estado]
            ?? ['label' => $this->estado, 'color' => 'bg-slate-100 text-slate-700 border-slate-200'];
    }

    public function numeroFormateado(): string
    {
        return $this->numero_factura
            ? "{$this->serie}-{$this->numero_factura}"
            : "{$this->serie}-BORRADOR";
    }
}
