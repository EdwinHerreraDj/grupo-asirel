<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /* =========================
     * RELACIONES
     * ========================= */

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function oficio()
    {
        return $this->belongsTo(
            ObraGastoCategoria::class,
            'obra_gasto_categoria_id'
        );
    }

    /* =========================
     * LÓGICA DE CÁLCULO
     * ========================= */

    protected static function booted()
    {
        static::saving(function ($presupuesto) {

            if (
                !is_null($presupuesto->cantidad) &&
                !is_null($presupuesto->precio_unitario)
            ) {
                $presupuesto->importe_total =
                    $presupuesto->cantidad * $presupuesto->precio_unitario;
            } else {
                $presupuesto->importe_total = null;
            }
        });
    }
}
