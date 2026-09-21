<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Empleado (Recursos humanos). Su documentación vive en el Drive, en la
 * carpeta `folder_id` (dentro de "Trabajadores" o "Trabajadores de baja").
 */
class Empleado extends Model
{
    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_BAJA = 'baja';

    public const TIPOS_CONTRATO = [
        'indefinido' => 'Indefinido',
        'temporal' => 'Temporal',
        'fijo_discontinuo' => 'Fijo discontinuo',
        'formacion' => 'Formación en alternancia',
        'practicas' => 'Prácticas',
        'otro' => 'Otro',
    ];

    public const JORNADAS = [
        'completa' => 'Completa',
        'parcial' => 'Parcial',
    ];

    public const MOTIVOS_BAJA = [
        'fin_contrato' => 'Fin de contrato',
        'baja_voluntaria' => 'Baja voluntaria',
        'despido' => 'Despido',
        'no_supera_prueba' => 'No supera el periodo de prueba',
        'jubilacion' => 'Jubilación',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'nombre',
        'apellidos',
        'dni',
        'nss',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'puesto',
        'categoria_convenio',
        'tipo_contrato',
        'jornada',
        'horas_semanales',
        'salario_bruto_anual',
        'dias_vacaciones_anuales',
        'iban',
        'contacto_emergencia_nombre',
        'contacto_emergencia_relacion',
        'contacto_emergencia_telefono',
        'folder_id',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'horas_semanales' => 'decimal:2',
        'salario_bruto_anual' => 'decimal:2',
        'dias_vacaciones_anuales' => 'float',
        'iban' => 'encrypted',
    ];

    protected $appends = ['nombre_completo'];

    /** Obras en las que trabaja (puede ser más de una a la vez). */
    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'empleado_obra')
            ->withTimestamps()
            ->orderBy('obras.nombre');
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /** Historial de altas y bajas, del más reciente al más antiguo. */
    public function periodos(): HasMany
    {
        return $this->hasMany(EmpleadoPeriodo::class)
            ->orderByDesc('fecha_alta')
            ->orderByDesc('id');
    }

    /** Periodo más reciente (el abierto si está de alta). */
    public function periodoActual(): HasOne
    {
        return $this->hasOne(EmpleadoPeriodo::class)->ofMany([
            'fecha_alta' => 'max',
            'id' => 'max',
        ]);
    }

    /** Vacaciones, bajas médicas, permisos… */
    public function ausencias(): HasMany
    {
        return $this->hasMany(Ausencia::class)->orderByDesc('fecha_inicio');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellidos);
    }

    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }
}
