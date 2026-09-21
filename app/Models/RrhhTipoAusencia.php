<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipo de ausencia (vacaciones, baja médica, asuntos propios…). Los que
 * tienen `dias_anuales` llevan saldo anual; el de vacaciones además se
 * prorratea según el tiempo de alta y admite días propios por empleado.
 */
class RrhhTipoAusencia extends Model
{
    public const COMPUTO_NATURALES = 'naturales';

    public const COMPUTO_LABORABLES = 'laborables';

    public const COLORES = [
        'cyan', 'sky', 'blue', 'indigo', 'violet', 'pink', 'rose',
        'red', 'orange', 'amber', 'emerald', 'teal', 'slate',
    ];

    protected $table = 'rrhh_tipos_ausencia';

    protected $fillable = [
        'nombre',
        'color',
        'es_vacaciones',
        'es_baja_medica',
        'retribuida',
        'requiere_justificante',
        'dias_anuales',
        'computo',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_vacaciones' => 'boolean',
        'es_baja_medica' => 'boolean',
        'retribuida' => 'boolean',
        'requiere_justificante' => 'boolean',
        'dias_anuales' => 'float',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
}
