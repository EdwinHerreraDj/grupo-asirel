<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObraPresupuestoVenta extends Model
{
    use HasFactory;

    protected $table = 'obra_presupuestos_venta';

    protected $fillable = [
        'obra_id',
        'obra_gasto_categoria_id',
        'unidad',
        'cantidad',
        'precio_unitario',
        'importe_total',
        'observaciones',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'precio_unitario' => 'float',
        'importe_total'   => 'float',
    ];

    // -------------------------
    // RELACIONES
    // -------------------------

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function oficio(): BelongsTo
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'obra_gasto_categoria_id');
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(PresupuestoVentaPartida::class, 'obra_presupuesto_venta_id')
            ->orderBy('orden')
            ->orderBy('id');
    }

    // -------------------------
    // CÁLCULO
    // El booted() original se mantiene intacto para compatibilidad.
    // recalcularDesdePartidas() es el método canónico cuando hay partidas.
    // -------------------------

    protected static function booted(): void
    {
        static::saving(function ($presupuesto) {
            if (
                !is_null($presupuesto->cantidad) &&
                !is_null($presupuesto->precio_unitario)
            ) {
                $presupuesto->importe_total =
                    round($presupuesto->cantidad * $presupuesto->precio_unitario, 2);
            } else {
                $presupuesto->importe_total = null;
            }
        });
    }

    /**
     * Recalcula importe_total sumando el importe de todas las partidas.
     * Se llama desde el booted() de PresupuestoVentaPartida.
     */
    public function recalcularDesdePartidas(): void
    {
        $total = $this->partidas()->sum('importe');

        // Actualizamos directo a BD sin pasar por el booted()
        // para evitar que sobrescriba con cantidad × precio_unitario
        static::withoutEvents(function () use ($total) {
            $this->update(['importe_total' => round($total, 2)]);
        });
    }
}
