<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Sanción disciplinaria de un empleado. */
class Sancion extends Model
{
    public const GRAVEDADES = [
        'leve' => 'Leve',
        'grave' => 'Grave',
        'muy_grave' => 'Muy grave',
    ];

    public const TIPOS = [
        'amonestacion_verbal' => 'Amonestación verbal',
        'amonestacion_escrita' => 'Amonestación por escrito',
        'suspension' => 'Suspensión de empleo y sueldo',
        'despido' => 'Despido disciplinario',
        'otra' => 'Otra',
    ];

    public const ESTADOS = [
        'vigente' => 'Vigente',
        'anulada' => 'Anulada',
    ];

    /**
     * Prescripción según el art. 60.2 del Estatuto de los Trabajadores: días
     * desde que la empresa conoce los hechos (orientativo).
     */
    public const PRESCRIPCION_DIAS = [
        'leve' => 10,
        'grave' => 20,
        'muy_grave' => 60,
    ];

    protected $table = 'rrhh_sanciones';

    protected $fillable = [
        'empleado_id', 'fecha_hechos', 'fecha_comunicacion', 'gravedad', 'tipo',
        'dias_suspension', 'fecha_inicio_suspension', 'descripcion', 'estado', 'file_id', 'usuario_id',
    ];

    protected $casts = [
        'fecha_hechos' => 'date:Y-m-d',
        'fecha_comunicacion' => 'date:Y-m-d',
        'fecha_inicio_suspension' => 'date:Y-m-d',
        'dias_suspension' => 'integer',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
