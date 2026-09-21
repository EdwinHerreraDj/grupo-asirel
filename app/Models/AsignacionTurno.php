<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Día del cuadrante: turno (y obra) de un empleado en una fecha. */
class AsignacionTurno extends Model
{
    protected $table = 'rrhh_cuadrante';

    protected $fillable = ['empleado_id', 'fecha', 'rrhh_turno_id', 'obra_id', 'observaciones', 'usuario_id'];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'rrhh_turno_id');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }
}
