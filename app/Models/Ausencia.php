<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ausencia de un empleado entre dos fechas (ambas incluidas). Una baja
 * médica puede quedar abierta (fecha_fin = null) hasta el alta médica.
 */
class Ausencia extends Model
{
    protected $table = 'rrhh_ausencias';

    protected $fillable = [
        'empleado_id',
        'rrhh_tipo_ausencia_id',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'file_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(RrhhTipoAusencia::class, 'rrhh_tipo_ausencia_id');
    }

    /** Justificante (parte de baja, etc.) guardado en el Drive. */
    public function archivo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /** Ausencias que tocan el intervalo [desde, hasta] (hasta null = sin límite). */
    public function scopeSolapadas(Builder $query, string $desde, ?string $hasta): Builder
    {
        return $query
            ->when($hasta, fn ($q) => $q->where('fecha_inicio', '<=', $hasta))
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $desde));
    }
}
