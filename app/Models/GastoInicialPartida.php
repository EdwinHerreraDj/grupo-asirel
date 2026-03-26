<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class GastoInicialPartida extends Model
{
    use HasFactory;

    protected $table = 'gasto_inicial_partidas';

    protected $fillable = [
        'obra_id',
        'obra_gasto_categoria_id',
        'presupuesto_venta_partida_id',
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

    // -------------------------
    // RELACIONES
    // -------------------------

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'obra_gasto_categoria_id');
    }

    public function partidaVenta(): BelongsTo
    {
        return $this->belongsTo(PresupuestoVentaPartida::class, 'presupuesto_venta_partida_id');
    }

    // -------------------------
    // CÁLCULO AUTOMÁTICO
    // -------------------------

    protected static function booted(): void
    {
        static::saving(function ($partida) {
            $partida->importe = round(
                ($partida->medicion ?? 0) * ($partida->precio_unitario ?? 0),
                2
            );
        });

        static::saved(function ($partida) {
            $partida->recalcularCapitulo();
        });

        static::deleted(function ($partida) {
            $partida->recalcularCapitulo();
        });
    }

    /**
     * Actualiza el importe total del capítulo en la pivot obra_gastos_iniciales.
     */
    public function recalcularCapitulo(): void
    {
        $total = static::where('obra_id', $this->obra_id)
            ->where('obra_gasto_categoria_id', $this->obra_gasto_categoria_id)
            ->sum('importe');

        DB::table('obra_gastos_iniciales')
            ->where('obra_id', $this->obra_id)
            ->where('obra_gasto_categoria_id', $this->obra_gasto_categoria_id)
            ->update(['importe' => round($total, 2)]);
    }

    // -------------------------
    // MARGEN vs. PARTIDA DE VENTA
    // -------------------------

    public function margen(): ?float
    {
        if (!$this->presupuesto_venta_partida_id || !$this->partidaVenta) {
            return null;
        }

        return round($this->partidaVenta->importe - $this->importe, 2);
    }

    public function margenPorcentaje(): ?float
    {
        if (!$this->partidaVenta || $this->partidaVenta->importe == 0) {
            return null;
        }

        return round(
            (($this->partidaVenta->importe - $this->importe) / $this->partidaVenta->importe) * 100,
            2
        );
    }
}
