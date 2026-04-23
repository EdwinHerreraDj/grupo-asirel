<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GastoInicialPartida extends Model
{
    use HasFactory;

    protected $table = 'gasto_inicial_partidas';

    protected $fillable = [
        'obra_id',
        'obra_gasto_categoria_id',
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

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(ObraGastoCategoria::class, 'obra_gasto_categoria_id');
    }

    public function partidasVentaLigadas(): HasMany
    {
        return $this->hasMany(PresupuestoVentaPartida::class, 'coste_partida_id');
    }

    protected static function booted(): void
    {
        static::saving(function ($partida) {
            $partida->importe = round(
                ($partida->medicion ?? 0) * ($partida->precio_unitario ?? 0),
                2
            );
        });
    }
}
