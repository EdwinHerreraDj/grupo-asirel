<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Periodo de alta de un empleado (de fecha_alta a fecha_baja). Un empleado
 * que vuelve a la empresa tiene varios periodos en la misma ficha.
 */
class EmpleadoPeriodo extends Model
{
    protected $table = 'empleado_periodos';

    protected $fillable = [
        'empleado_id',
        'fecha_alta',
        'fecha_baja',
        'tipo_contrato',
        'motivo_baja',
        'observaciones_baja',
    ];

    protected $casts = [
        'fecha_alta' => 'date:Y-m-d',
        'fecha_baja' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
