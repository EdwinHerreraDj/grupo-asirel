<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Anticipo o vale entregado a cuenta. Queda pendiente hasta que se descuenta
 * en una nómina (nomina_id).
 */
class Anticipo extends Model
{
    public const TIPOS = [
        'anticipo' => 'Anticipo',
        'vale' => 'Vale',
    ];

    public const FORMAS_PAGO = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'otro' => 'Otro',
    ];

    protected $table = 'rrhh_anticipos';

    protected $fillable = [
        'empleado_id', 'tipo', 'fecha', 'importe', 'concepto', 'forma_pago',
        'nomina_id', 'file_id', 'observaciones', 'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'importe' => 'decimal:2',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
