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
        'estado_cobro',
        'estado_cobro_actualizado_at',
        'estado_cobro_actualizado_por',
        'pdf_url',
        'pdf_original_generado_at',
        'adjunto',
        'observaciones',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_emision'  => 'date',
        'fecha_contable' => 'date',
        'vencimiento'    => 'date',
        'pdf_original_generado_at'    => 'datetime',
        'estado_cobro_actualizado_at' => 'datetime',

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

    public function reimpresiones(): HasMany
    {
        return $this->hasMany(FacturaVentaReimpresion::class)
            ->latest();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(FacturaVentaDocumento::class, 'factura_venta_id')
            ->latest();
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

    /* =========================
     * PDF: ORIGINAL vs COPIA
     * ========================= */

    /** ¿Existe un PDF original emitido y su archivo en disco? */
    public function tienePdfOriginal(): bool
    {
        return ! empty($this->pdf_url)
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->pdf_url);
    }

    /**
     * El PDF original es INMUTABLE una vez congelado (se registró
     * `pdf_original_generado_at`) y la factura no es borrador. En borrador
     * todavía puede regenerarse libremente (no es documento fiscal).
     */
    public function pdfOriginalEsInmutable(): bool
    {
        return $this->estado !== self::ESTADO_BORRADOR
            && ! is_null($this->pdf_original_generado_at);
    }

    /**
     * Se puede generar una COPIA / reimpresión de cualquier factura ya emitida
     * (emitida, enviada, pagada o anulada). El borrador aún no es documento
     * fiscal cerrado, así que no aplica el concepto de "copia".
     */
    public function puedeGenerarCopia(): bool
    {
        return $this->estado !== self::ESTADO_BORRADOR;
    }

    /* =========================
     * ESTADO INFORMATIVO DE COBRO (capa de seguimiento, no fiscal)
     * ========================= */

    /**
     * El seguimiento de cobro solo tiene sentido en facturas ya emitidas y
     * vigentes. En borrador (aún no emitida) y en anulada (documento sin valor)
     * queda fijado, sin clasificación editable.
     */
    public function puedeGestionarEstadoCobro(): bool
    {
        return in_array($this->estado, [
            self::ESTADO_EMITIDA,
            self::ESTADO_ENVIADA,
            self::ESTADO_PAGADA,
        ], true);
    }

    public function estadoCobroMeta(): array
    {
        return \App\Support\EstadoCobro::meta($this->estado_cobro);
    }

    public function estadoCobroActualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estado_cobro_actualizado_por');
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
