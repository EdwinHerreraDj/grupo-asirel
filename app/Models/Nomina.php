<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nómina de un empleado (la calcula la gestoría; aquí se registran sus
 * importes, el PDF y si está pagada).
 */
class Nomina extends Model
{
    public const TIPOS = [
        'mensual' => 'Mensual',
        'extra' => 'Paga extra',
        'finiquito' => 'Finiquito',
        'atrasos' => 'Atrasos',
    ];

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_PAGADA = 'pagada';

    protected $table = 'rrhh_nominas';

    protected $fillable = [
        'empleado_id', 'anio', 'mes', 'tipo',
        'bruto', 'irpf', 'seguridad_social', 'anticipos', 'otras_deducciones', 'neto', 'coste_empresa',
        'estado', 'fecha_pago', 'file_id', 'observaciones', 'usuario_id',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes' => 'integer',
        'bruto' => 'decimal:2',
        'irpf' => 'decimal:2',
        'seguridad_social' => 'decimal:2',
        'anticipos' => 'decimal:2',
        'otras_deducciones' => 'decimal:2',
        'neto' => 'decimal:2',
        'coste_empresa' => 'decimal:2',
        'fecha_pago' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /** Anticipos y vales descontados en esta nómina. */
    public function anticiposDescontados(): HasMany
    {
        return $this->hasMany(Anticipo::class, 'nomina_id')->orderBy('fecha');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
