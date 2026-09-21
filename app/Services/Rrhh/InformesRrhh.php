<?php

namespace App\Services\Rrhh;

use App\Http\Controllers\Api\Rrhh\CursoController;
use App\Models\AsignacionTurno;
use App\Models\Ausencia;
use App\Models\Curso;
use App\Models\Empleado;
use App\Models\EmpleadoPeriodo;
use App\Models\Nomina;
use App\Models\RrhhTipoAusencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Cifras e informes de Recursos humanos. Cada informe devuelve
 * [titulo, cabecera, filas] para exportarlo a Excel.
 */
class InformesRrhh
{
    public const TIPOS = [
        'plantilla' => 'Plantilla',
        'altas-bajas' => 'Altas y bajas',
        'ausencias' => 'Ausencias',
        'vacaciones' => 'Vacaciones',
        'nominas' => 'Nóminas',
        'horas-obra' => 'Horas por obra',
        'formacion' => 'Formación',
    ];

    public function __construct(
        private readonly CalendarioLaboral $calendario,
    ) {}

    /** Cifras del año para el panel de informes. */
    public function resumen(int $anio): array
    {
        $ini = "{$anio}-01-01";
        $fin = "{$anio}-12-31";
        $hoy = CarbonImmutable::today();
        $corte = CarbonImmutable::create($anio, 12, 31)->min($hoy);

        $nominas = Nomina::where('anio', $anio)->get();
        $porMes = [];
        for ($m = 1; $m <= 12; $m++) {
            $delMes = $nominas->where('mes', $m);
            $porMes[] = [
                'mes' => $m,
                'bruto' => round($delMes->sum(fn ($n) => (float) $n->bruto), 2),
                'coste' => round($delMes->sum(fn ($n) => (float) ($n->coste_empresa ?? $n->bruto)), 2),
            ];
        }

        // Absentismo: días laborables de baja médica / días laborables de alta (hasta hoy).
        [$diasBaja, $diasAlta] = $this->absentismo($anio, $corte);

        $ausenciasPorTipo = Ausencia::solapadas($ini, $fin)->with('tipo:id,nombre,color')->get()
            ->groupBy('rrhh_tipo_ausencia_id')
            ->map(function (Collection $lista) use ($ini, $fin) {
                $dias = $lista->sum(fn ($a) => $this->calendario->diasDe($a, CarbonImmutable::parse($ini), CarbonImmutable::parse($fin))['naturales']);

                return ['tipo' => $lista->first()->tipo->nombre, 'color' => $lista->first()->tipo->color, 'dias' => $dias, 'ausencias' => $lista->count()];
            })
            ->sortByDesc('dias')
            ->values();

        $horasObra = $this->horasPorObra($ini, $fin)
            ->groupBy('obra')
            ->map(fn ($filas, $obra) => ['obra' => $obra, 'horas' => round($filas->sum('horas'), 1), 'empleados' => $filas->count()])
            ->sortByDesc('horas')
            ->values();

        return [
            'anio' => $anio,
            'plantilla' => Empleado::where('estado', Empleado::ESTADO_ACTIVO)->count(),
            'altas' => EmpleadoPeriodo::whereBetween('fecha_alta', [$ini, $fin])->count(),
            'bajas' => EmpleadoPeriodo::whereBetween('fecha_baja', [$ini, $fin])->count(),
            'absentismo' => $diasAlta > 0 ? round($diasBaja * 100 / $diasAlta, 2) : 0,
            'dias_baja_medica' => $diasBaja,
            'nominas' => [
                'bruto' => round($nominas->sum(fn ($n) => (float) $n->bruto), 2),
                'neto' => round($nominas->sum(fn ($n) => (float) $n->neto), 2),
                'coste_empresa' => round($nominas->sum(fn ($n) => (float) ($n->coste_empresa ?? 0)), 2),
                'registradas' => $nominas->count(),
                'por_mes' => $porMes,
            ],
            'horas_planificadas' => round($horasObra->sum('horas'), 1),
            'horas_obra' => $horasObra->take(8)->values(),
            'ausencias_por_tipo' => $ausenciasPorTipo,
            'informes' => self::TIPOS,
        ];
    }

    /** @return array{0: string, 1: array, 2: array} */
    public function tabla(string $tipo, array $p): array
    {
        return match ($tipo) {
            'plantilla' => $this->plantilla($p['estado'] ?? 'activo'),
            'altas-bajas' => $this->altasBajas($p['desde'], $p['hasta']),
            'ausencias' => $this->ausencias($p['desde'], $p['hasta']),
            'vacaciones' => $this->vacaciones((int) $p['anio']),
            'nominas' => $this->nominas((int) $p['anio']),
            'horas-obra' => $this->horasObra($p['desde'], $p['hasta']),
            'formacion' => $this->formacion(),
        };
    }

    // -------------------------------------------------------------

    private function plantilla(string $estado): array
    {
        $empleados = Empleado::query()
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado === 'baja' ? Empleado::ESTADO_BAJA : Empleado::ESTADO_ACTIVO))
            ->with(['obras:obras.id,obras.nombre', 'periodoActual'])
            ->orderBy('apellidos')->orderBy('nombre')->get();

        return ['Plantilla', [
            'Apellidos', 'Nombre', 'DNI/NIE', 'Nº Seg. Social', 'Puesto', 'Categoría', 'Contrato', 'Jornada', 'Horas/semana',
            'Salario bruto anual', 'Obras', 'Alta actual', 'Fin de contrato', 'Estado', 'Teléfono', 'Email', 'Población',
        ], $empleados->map(fn (Empleado $e) => [
            $e->apellidos, $e->nombre, $e->dni, $e->nss, $e->puesto, $e->categoria_convenio,
            Empleado::TIPOS_CONTRATO[$e->tipo_contrato] ?? $e->tipo_contrato,
            Empleado::JORNADAS[$e->jornada] ?? $e->jornada,
            $e->horas_semanales !== null ? (float) $e->horas_semanales : null,
            $e->salario_bruto_anual !== null ? (float) $e->salario_bruto_anual : null,
            $e->obras->pluck('nombre')->implode(', '),
            $e->periodoActual?->fecha_alta?->format('d/m/Y'),
            $e->periodoActual?->fecha_fin_contrato?->format('d/m/Y'),
            $e->estado === Empleado::ESTADO_ACTIVO ? 'De alta' : 'De baja',
            $e->telefono, $e->email, $e->poblacion,
        ])->all()];
    }

    private function altasBajas(string $desde, string $hasta): array
    {
        $periodos = EmpleadoPeriodo::with('empleado')
            ->where(fn ($q) => $q->whereBetween('fecha_alta', [$desde, $hasta])->orWhereBetween('fecha_baja', [$desde, $hasta]))
            ->orderBy('fecha_alta')->get();

        return ['Altas y bajas', [
            'Empleado', 'DNI/NIE', 'Fecha de alta', 'Fecha de baja', 'Motivo de baja', 'Contrato', 'Días en la empresa',
        ], $periodos->map(fn (EmpleadoPeriodo $p) => [
            $p->empleado?->apellidos.', '.$p->empleado?->nombre,
            $p->empleado?->dni,
            $p->fecha_alta->format('d/m/Y'),
            $p->fecha_baja?->format('d/m/Y'),
            Empleado::MOTIVOS_BAJA[$p->motivo_baja] ?? $p->motivo_baja,
            Empleado::TIPOS_CONTRATO[$p->tipo_contrato] ?? $p->tipo_contrato,
            (int) $p->fecha_alta->diffInDays($p->fecha_baja ?? today()) + 1,
        ])->all()];
    }

    private function ausencias(string $desde, string $hasta): array
    {
        $d = CarbonImmutable::parse($desde);
        $h = CarbonImmutable::parse($hasta);
        $ausencias = Ausencia::solapadas($desde, $hasta)->with(['empleado', 'tipo'])->orderBy('fecha_inicio')->get();

        return ['Ausencias', [
            'Empleado', 'DNI/NIE', 'Tipo', 'Desde', 'Hasta', 'Días naturales (en el periodo)', 'Días laborables (en el periodo)', 'Retribuida', 'Observaciones',
        ], $ausencias->map(function (Ausencia $a) use ($d, $h) {
            $dias = $this->calendario->diasDe($a, $d, $h);

            return [
                $a->empleado->apellidos.', '.$a->empleado->nombre,
                $a->empleado->dni,
                $a->tipo->nombre,
                $a->fecha_inicio->format('d/m/Y'),
                $a->fecha_fin?->format('d/m/Y') ?? 'Abierta',
                $dias['naturales'],
                $dias['laborables'],
                $a->tipo->retribuida ? 'Sí' : 'No',
                $a->observaciones,
            ];
        })->all()];
    }

    private function vacaciones(int $anio): array
    {
        $tipo = RrhhTipoAusencia::where('es_vacaciones', true)->whereNotNull('dias_anuales')->first();
        $empleados = Empleado::whereHas('periodos', fn ($q) => $q->where('fecha_alta', '<=', "{$anio}-12-31")
            ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', "{$anio}-01-01")))
            ->with('periodos')->orderBy('apellidos')->orderBy('nombre')->get();

        return ["Vacaciones {$anio}", [
            'Empleado', 'DNI/NIE', 'Días/año', 'Le corresponden', 'Disfrutados', 'Programados', 'Pendientes', 'Proporcional',
        ], $tipo ? $empleados->map(function (Empleado $e) use ($anio, $tipo) {
            $s = $this->calendario->saldos($e, $anio, collect([$tipo]))[0];

            return [
                $e->apellidos.', '.$e->nombre, $e->dni, $s['anuales'], $s['devengados'],
                $s['disfrutados'], $s['programados'], $s['pendientes'], $s['proporcional'] ? 'Sí' : 'No',
            ];
        })->all() : []];
    }

    private function nominas(int $anio): array
    {
        $nominas = Nomina::where('anio', $anio)->with('empleado')->orderBy('mes')->get()
            ->sortBy(fn ($n) => sprintf('%02d %s', $n->mes, $n->empleado->apellidos))->values();

        return ["Nóminas {$anio}", [
            'Mes', 'Empleado', 'DNI/NIE', 'Tipo', 'Bruto', 'IRPF', 'Seg. Social', 'Anticipos', 'Otras deducciones', 'Neto', 'Coste empresa', 'Estado', 'Fecha de pago',
        ], $nominas->map(fn (Nomina $n) => [
            sprintf('%02d/%d', $n->mes, $n->anio),
            $n->empleado->apellidos.', '.$n->empleado->nombre,
            $n->empleado->dni,
            Nomina::TIPOS[$n->tipo] ?? $n->tipo,
            (float) $n->bruto, (float) $n->irpf, (float) $n->seguridad_social, (float) $n->anticipos,
            (float) $n->otras_deducciones, (float) $n->neto,
            $n->coste_empresa !== null ? (float) $n->coste_empresa : null,
            $n->estado === Nomina::ESTADO_PAGADA ? 'Pagada' : 'Pendiente',
            $n->fecha_pago?->format('d/m/Y'),
        ])->all()];
    }

    private function horasObra(string $desde, string $hasta): array
    {
        return ['Horas por obra', ['Obra', 'Empleado', 'DNI/NIE', 'Días', 'Horas'],
            $this->horasPorObra($desde, $hasta)
                ->sortBy(fn ($f) => $f['obra'].' '.$f['empleado'])
                ->map(fn ($f) => [$f['obra'], $f['empleado'], $f['dni'], $f['dias'], $f['horas']])
                ->values()->all(),
        ];
    }

    private function formacion(): array
    {
        $cursos = CursoController::conEstado(Curso::with('empleado')->orderBy('fecha')->get());
        $estados = ['vigente' => 'Vigente', 'sin_caducidad' => 'Sin caducidad', 'proximo' => 'Caduca pronto', 'vencido' => 'Caducado', 'renovado' => 'Renovado'];

        return ['Formación', ['Empleado', 'DNI/NIE', 'Estado del empleado', 'Curso', 'Categoría', 'Entidad', 'Horas', 'Fecha', 'Caduca', 'Estado'],
            $cursos->sortBy(fn ($c) => $c->empleado->apellidos)->map(fn (Curso $c) => [
                $c->empleado->apellidos.', '.$c->empleado->nombre,
                $c->empleado->dni,
                $c->empleado->estado === Empleado::ESTADO_ACTIVO ? 'De alta' : 'De baja',
                $c->nombre,
                Curso::CATEGORIAS[$c->categoria] ?? $c->categoria,
                $c->entidad,
                $c->horas,
                $c->fecha->format('d/m/Y'),
                $c->fecha_caducidad?->format('d/m/Y'),
                $estados[$c->estado] ?? $c->estado,
            ])->values()->all(),
        ];
    }

    /** Filas {obra, empleado, dni, dias, horas} del cuadrante en el rango. */
    private function horasPorObra(string $desde, string $hasta): Collection
    {
        return AsignacionTurno::whereBetween('fecha', [$desde, $hasta])
            ->with(['turno', 'obra:id,nombre', 'empleado:id,nombre,apellidos,dni'])
            ->get()
            ->groupBy(fn ($a) => ($a->obra_id ?? 0).'|'.$a->empleado_id)
            ->map(fn (Collection $filas) => [
                'obra' => $filas->first()->obra?->nombre ?? 'Sin obra',
                'empleado' => $filas->first()->empleado->apellidos.', '.$filas->first()->empleado->nombre,
                'dni' => $filas->first()->empleado->dni,
                'dias' => $filas->count(),
                'horas' => round($filas->sum(fn ($a) => $a->turno->horas), 2),
            ])
            ->values();
    }

    /** @return array{0: int, 1: int} [días laborables de baja médica, días laborables de alta] */
    private function absentismo(int $anio, CarbonImmutable $corte): array
    {
        $ini = CarbonImmutable::create($anio, 1, 1);
        if ($corte->lt($ini)) {
            return [0, 0];
        }

        $diasAlta = 0;
        EmpleadoPeriodo::where('fecha_alta', '<=', $corte->toDateString())
            ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $ini->toDateString()))
            ->get()
            ->each(function ($p) use ($ini, $corte, &$diasAlta) {
                $desde = CarbonImmutable::parse($p->fecha_alta)->max($ini);
                $hasta = ($p->fecha_baja ? CarbonImmutable::parse($p->fecha_baja) : $corte)->min($corte);
                $diasAlta += $this->calendario->contar($desde, $hasta, RrhhTipoAusencia::COMPUTO_LABORABLES);
            });

        $diasBaja = Ausencia::solapadas($ini->toDateString(), $corte->toDateString())
            ->whereHas('tipo', fn ($q) => $q->where('es_baja_medica', true))
            ->get()
            ->sum(fn ($a) => $this->calendario->diasDe($a, $ini, $corte)['laborables']);

        return [$diasBaja, $diasAlta];
    }
}
