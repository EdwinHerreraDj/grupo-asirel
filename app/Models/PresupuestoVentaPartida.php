<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresupuestoVentaPartida extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_venta_partidas';

    protected $fillable = [
        'obra_presupuesto_venta_id',
        'obra_id',
        'coste_partida_id',
        'codigo',
        'descripcion',
        'unidad',
        'medicion',
        'precio_unitario',
        'importe',
        'orden',
        'activo',
    ];

    protected $casts = [
        'medicion'        => 'float',
        'precio_unitario' => 'float',
        'importe'         => 'float',
        'activo'          => 'boolean',
    ];

    public function capitulo(): BelongsTo
    {
        return $this->belongsTo(ObraPresupuestoVenta::class, 'obra_presupuesto_venta_id');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function costePartida(): BelongsTo
    {
        return $this->belongsTo(GastoInicialPartida::class, 'coste_partida_id');
    }

    public function certificacionDetalles(): HasMany
    {
        return $this->hasMany(CertificacionDetalle::class);
    }

    /**
     * Indica si la partida est\u00e1 bloqueada para edici\u00f3n de campos descriptivos
     * porque ya ha sido referenciada por alg\u00fan detalle de certificaci\u00f3n.
     */
    public function estaCertificada(): bool
    {
        return $this->certificacionDetalles()->exists();
    }

    protected static function booted(): void
    {
        static::saving(function ($partida) {
            $partida->importe = round(
                ($partida->medicion ?? 0) * ($partida->precio_unitario ?? 0),
                2
            );
        });

        static::saved(function ($partida) {
            $partida->capitulo->recalcularDesdePartidas();
        });

        static::deleted(function ($partida) {
            $partida->capitulo->recalcularDesdePartidas();
        });
    }
}
