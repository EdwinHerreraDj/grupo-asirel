<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    // Mantener nombres de relaciones en camelCase al serializar para
    // evitar colisión con los FKs `asignado_a` y `creado_por`.
    public static $snakeAttributes = false;

    public const ESTADO_PENDIENTE  = 'pendiente';
    public const ESTADO_EN_CURSO   = 'en_curso';
    public const ESTADO_COMPLETADA = 'completada';

    public const ESTADOS_META = [
        self::ESTADO_PENDIENTE => [
            'label' => 'Pendiente',
            'color' => 'bg-slate-100 text-slate-700 border-slate-200',
            'icon'  => 'mgc_time_line',
        ],
        self::ESTADO_EN_CURSO => [
            'label' => 'En curso',
            'color' => 'bg-blue-100 text-blue-700 border-blue-200',
            'icon'  => 'mgc_loading_3_line',
        ],
        self::ESTADO_COMPLETADA => [
            'label' => 'Completada',
            'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'icon'  => 'mgc_check_line',
        ],
    ];

    public const PRIORIDAD_BAJA  = 'baja';
    public const PRIORIDAD_MEDIA = 'media';
    public const PRIORIDAD_ALTA  = 'alta';

    public const PRIORIDADES_META = [
        self::PRIORIDAD_BAJA => [
            'label' => 'Baja',
            'color' => 'bg-slate-100 text-slate-700 border-slate-200',
        ],
        self::PRIORIDAD_MEDIA => [
            'label' => 'Media',
            'color' => 'bg-amber-100 text-amber-700 border-amber-200',
        ],
        self::PRIORIDAD_ALTA => [
            'label' => 'Alta',
            'color' => 'bg-red-100 text-red-700 border-red-200',
        ],
    ];

    protected $fillable = [
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_limite',
        'completada_en',
        'obra_id',
        'asignado_a',
        'creado_por',
    ];

    protected $casts = [
        'fecha_limite'  => 'date',
        'completada_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Auto setear/limpiar completada_en cuando cambia el estado
        static::saving(function (Tarea $tarea) {
            if ($tarea->isDirty('estado')) {
                if ($tarea->estado === self::ESTADO_COMPLETADA && ! $tarea->completada_en) {
                    $tarea->completada_en = now();
                } elseif ($tarea->estado !== self::ESTADO_COMPLETADA) {
                    $tarea->completada_en = null;
                }
            }
        });
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function getVencidaAttribute(): bool
    {
        return $this->fecha_limite
            && $this->estado !== self::ESTADO_COMPLETADA
            && $this->fecha_limite->isPast();
    }

    public function getProximaAVencerAttribute(): bool
    {
        if (! $this->fecha_limite || $this->estado === self::ESTADO_COMPLETADA) {
            return false;
        }
        $diasRestantes = now()->startOfDay()->diffInDays($this->fecha_limite, false);

        return $diasRestantes >= 0 && $diasRestantes <= 3;
    }
}
