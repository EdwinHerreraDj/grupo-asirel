<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoVentaPartida extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_venta_partidas';

    protected $fillable = [
        'obra_presupuesto_venta_id',
        'obra_id',
        'codigo',
        'descripcion',
        'gasto_inicial_partida_id',
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

    public function capitulo(): BelongsTo
    {
        return $this->belongsTo(ObraPresupuestoVenta::class, 'obra_presupuesto_venta_id');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    // -------------------------
    // CÁLCULO AUTOMÁTICO
    // -------------------------

    public function gastoInicialPartida(): BelongsTo
    {
        return $this->belongsTo(GastoInicialPartida::class, 'gasto_inicial_partida_id');
    }

    protected static function booted(): void
    {
        // Calcular importe antes de guardar
        static::saving(function ($partida) {
            $partida->importe = round(
                ($partida->medicion ?? 0) * ($partida->precio_unitario ?? 0),
                2
            );
        });

        // Después de guardar → recalcular total del capítulo
        static::saved(function ($partida) {
            $partida->capitulo->recalcularDesdePartidas();
        });

        // Después de eliminar → recalcular total del capítulo
        static::deleted(function ($partida) {
            $partida->capitulo->recalcularDesdePartidas();
        });
    }
}
