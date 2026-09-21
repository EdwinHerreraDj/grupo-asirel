<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipo de turno (horario). Admite jornada partida (segundo tramo) y un
 * descanso no retribuido en minutos.
 */
class Turno extends Model
{
    protected $table = 'rrhh_turnos';

    protected $fillable = [
        'nombre', 'color', 'hora_inicio', 'hora_fin', 'hora_inicio_2', 'hora_fin_2',
        'descanso_minutos', 'orden', 'activo',
    ];

    protected $casts = [
        'descanso_minutos' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    protected $appends = ['horas', 'horario'];

    /** Horas de trabajo del turno (un tramo que pasa de medianoche suma 24 h). */
    public function getHorasAttribute(): float
    {
        $minutos = self::minutos($this->hora_inicio, $this->hora_fin)
            + ($this->hora_inicio_2 && $this->hora_fin_2 ? self::minutos($this->hora_inicio_2, $this->hora_fin_2) : 0)
            - (int) $this->descanso_minutos;

        return round(max(0, $minutos) / 60, 2);
    }

    /** "08:00–13:00 / 14:00–17:00" */
    public function getHorarioAttribute(): string
    {
        $h = fn ($v) => $v ? substr($v, 0, 5) : '';
        $texto = $h($this->hora_inicio).'–'.$h($this->hora_fin);

        if ($this->hora_inicio_2 && $this->hora_fin_2) {
            $texto .= ' / '.$h($this->hora_inicio_2).'–'.$h($this->hora_fin_2);
        }

        return $texto;
    }

    private static function minutos(?string $inicio, ?string $fin): int
    {
        if (! $inicio || ! $fin) {
            return 0;
        }

        [$hi, $mi] = array_map('intval', explode(':', $inicio));
        [$hf, $mf] = array_map('intval', explode(':', $fin));
        $diff = ($hf * 60 + $mf) - ($hi * 60 + $mi);

        return $diff <= 0 ? $diff + 24 * 60 : $diff;
    }
}
