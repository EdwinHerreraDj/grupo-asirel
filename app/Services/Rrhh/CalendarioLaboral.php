<?php

namespace App\Services\Rrhh;

use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\Festivo;
use App\Models\RrhhTipoAusencia;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Cuentas de días del módulo de ausencias:
 *  - días naturales y laborables (lunes a viernes que no son festivo);
 *  - saldo anual de los tipos con días por año (vacaciones prorrateadas
 *    según el tiempo de alta en el año);
 *  - festivos nacionales de España de un año.
 */
class CalendarioLaboral
{
    /** @var array<int, array<string, string>> año => ['Y-m-d' => nombre] */
    private array $festivos = [];

    /** @return array<string, string> ['Y-m-d' => nombre] */
    public function festivosDelAnio(int $anio): array
    {
        return $this->festivos[$anio] ??= Festivo::whereYear('fecha', $anio)
            ->orderBy('fecha')
            ->get()
            ->mapWithKeys(fn (Festivo $f) => [$f->fecha->toDateString() => $f->nombre])
            ->all();
    }

    public function esFestivo(CarbonImmutable $dia): bool
    {
        return isset($this->festivosDelAnio($dia->year)[$dia->toDateString()]);
    }

    /** Días entre dos fechas (incluidas) según el cómputo. */
    public function contar(CarbonImmutable $desde, CarbonImmutable $hasta, string $computo): int
    {
        if ($hasta->lt($desde)) {
            return 0;
        }

        if ($computo !== RrhhTipoAusencia::COMPUTO_LABORABLES) {
            return (int) $desde->diffInDays($hasta) + 1;
        }

        $dias = 0;
        for ($d = $desde; $d->lte($hasta); $d = $d->addDay()) {
            if (! $d->isWeekend() && ! $this->esFestivo($d)) {
                $dias++;
            }
        }

        return $dias;
    }

    /**
     * Días de una ausencia (una baja abierta cuenta hasta hoy), opcionalmente
     * recortada a [desde, hasta].
     *
     * @return array{naturales: int, laborables: int}
     */
    public function diasDe(Ausencia $ausencia, ?CarbonImmutable $desde = null, ?CarbonImmutable $hasta = null): array
    {
        $inicio = CarbonImmutable::parse($ausencia->fecha_inicio)->startOfDay();
        $fin = $ausencia->fecha_fin
            ? CarbonImmutable::parse($ausencia->fecha_fin)->startOfDay()
            : CarbonImmutable::today()->max($inicio);

        if ($desde && $desde->gt($inicio)) {
            $inicio = $desde;
        }
        if ($hasta && $hasta->lt($fin)) {
            $fin = $hasta;
        }

        return [
            'naturales' => $this->contar($inicio, $fin, RrhhTipoAusencia::COMPUTO_NATURALES),
            'laborables' => $this->contar($inicio, $fin, RrhhTipoAusencia::COMPUTO_LABORABLES),
        ];
    }

    /** Días que el empleado está de alta en el año (un alta abierta llega a fin de año). */
    public function diasDeAltaEnAnio(Empleado $empleado, int $anio): int
    {
        $ini = CarbonImmutable::create($anio, 1, 1);
        $fin = CarbonImmutable::create($anio, 12, 31);
        $dias = 0;

        foreach ($empleado->periodos as $p) {
            $desde = CarbonImmutable::parse($p->fecha_alta)->max($ini);
            $hasta = ($p->fecha_baja ? CarbonImmutable::parse($p->fecha_baja) : $fin)->min($fin);
            $dias += $this->contar($desde, $hasta, RrhhTipoAusencia::COMPUTO_NATURALES);
        }

        return min($dias, $ini->daysInYear);
    }

    /**
     * Saldo del año de cada tipo con días por año.
     *
     * @return array<int, array> [{tipo_id, tipo, color, computo, anuales, devengados, disfrutados, programados, pendientes, proporcional}]
     */
    public function saldos(Empleado $empleado, int $anio, ?Collection $tipos = null): array
    {
        $tipos ??= RrhhTipoAusencia::where('activo', true)->whereNotNull('dias_anuales')->orderBy('orden')->get();
        $empleado->loadMissing('periodos');

        $ini = CarbonImmutable::create($anio, 1, 1);
        $fin = CarbonImmutable::create($anio, 12, 31);
        $hoy = CarbonImmutable::today();

        $ausencias = Ausencia::where('empleado_id', $empleado->id)
            ->whereIn('rrhh_tipo_ausencia_id', $tipos->pluck('id'))
            ->solapadas($ini->toDateString(), $fin->toDateString())
            ->get()
            ->groupBy('rrhh_tipo_ausencia_id');

        $diasAlta = $this->diasDeAltaEnAnio($empleado, $anio);

        return $tipos->map(function (RrhhTipoAusencia $tipo) use ($empleado, $anio, $ini, $fin, $hoy, $ausencias, $diasAlta) {
            $anuales = $tipo->es_vacaciones && $empleado->dias_vacaciones_anuales !== null
                ? (float) $empleado->dias_vacaciones_anuales
                : (float) $tipo->dias_anuales;

            $devengados = $tipo->es_vacaciones
                ? round($anuales * $diasAlta / $ini->daysInYear, 1)
                : $anuales;

            $disfrutados = 0;
            $programados = 0;
            foreach ($ausencias->get($tipo->id, collect()) as $a) {
                $inicio = CarbonImmutable::parse($a->fecha_inicio)->max($ini);
                $final = ($a->fecha_fin ? CarbonImmutable::parse($a->fecha_fin) : $hoy->max($inicio))->min($fin);

                $disfrutados += $this->contar($inicio, $final->min($hoy), $tipo->computo);
                $programados += $this->contar($inicio->max($hoy->addDay()), $final, $tipo->computo);
            }

            return [
                'tipo_id' => $tipo->id,
                'tipo' => $tipo->nombre,
                'color' => $tipo->color,
                'computo' => $tipo->computo,
                'anuales' => $anuales,
                'devengados' => $devengados,
                'disfrutados' => $disfrutados,
                'programados' => $programados,
                'pendientes' => round($devengados - $disfrutados - $programados, 1),
                'proporcional' => $tipo->es_vacaciones && $diasAlta < $ini->daysInYear,
                'personalizado' => $tipo->es_vacaciones && $empleado->dias_vacaciones_anuales !== null,
            ];
        })->values()->all();
    }

    /**
     * Festivos nacionales comunes a toda España (las comunidades pueden
     * trasladar alguno; los autonómicos y locales se añaden a mano).
     *
     * @return array<string, string> ['Y-m-d' => nombre]
     */
    public static function festivosNacionales(int $anio): array
    {
        $viernesSanto = self::domingoDePascua($anio)->subDays(2);

        $lista = [
            "{$anio}-01-01" => 'Año Nuevo',
            "{$anio}-01-06" => 'Epifanía del Señor',
            $viernesSanto->toDateString() => 'Viernes Santo',
            "{$anio}-05-01" => 'Fiesta del Trabajo',
            "{$anio}-08-15" => 'Asunción de la Virgen',
            "{$anio}-10-12" => 'Fiesta Nacional de España',
            "{$anio}-11-01" => 'Todos los Santos',
            "{$anio}-12-06" => 'Día de la Constitución',
            "{$anio}-12-08" => 'Inmaculada Concepción',
            "{$anio}-12-25" => 'Natividad del Señor',
        ];
        ksort($lista);

        return $lista;
    }

    /** Algoritmo de Meeus/Jones/Butcher (calendario gregoriano). */
    public static function domingoDePascua(int $anio): Carbon
    {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($anio, $mes, $dia)->startOfDay();
    }
}
