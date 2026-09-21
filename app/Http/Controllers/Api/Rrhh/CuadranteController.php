<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\AsignacionTurno;
use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\RrhhTipoAusencia;
use App\Models\Turno;
use App\Services\Rrhh\CalendarioLaboral;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Cuadrante de turnos: qué turno y en qué obra trabaja cada empleado cada
 * día. Al asignar se saltan los días en que no está de alta, los que tiene
 * una ausencia y (salvo que se pida) los festivos.
 */
class CuadranteController extends Controller
{
    private const MAX_DIAS_VISTA = 42;

    private const MAX_DIAS_ASIGNAR = 92;

    public function __construct(
        private readonly CalendarioLaboral $calendario,
    ) {}

    public function index(Request $request)
    {
        $desde = $request->filled('desde')
            ? CarbonImmutable::parse($request->input('desde'))->startOfDay()
            : CarbonImmutable::today()->startOfWeek();
        $hasta = $request->filled('hasta')
            ? CarbonImmutable::parse($request->input('hasta'))->startOfDay()
            : $desde->addDays(6);

        if ($hasta->lt($desde) || $desde->diffInDays($hasta) >= self::MAX_DIAS_VISTA) {
            $hasta = $desde->addDays(6);
        }
        [$d, $h] = [$desde->toDateString(), $hasta->toDateString()];

        $query = Empleado::query()
            ->whereHas('periodos', fn ($q) => $q->where('fecha_alta', '<=', $h)
                ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $d)))
            ->with([
                'periodos' => fn ($q) => $q->where('fecha_alta', '<=', $h)
                    ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $d)),
                'obras:obras.id,obras.nombre',
            ]);

        if ($busqueda = trim((string) $request->input('search'))) {
            $like = '%'.addcslashes($busqueda, '%_\\').'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $like)
                ->orWhere('apellidos', 'like', $like)
                ->orWhere('dni', 'like', $like)
                ->orWhere('puesto', 'like', $like));
        }

        if ($request->filled('obra_id')) {
            $obraId = (int) $request->input('obra_id');
            $query->where(fn ($q) => $q
                ->whereHas('obras', fn ($q) => $q->where('obras.id', $obraId))
                ->orWhereExists(fn ($q) => $q->from('rrhh_cuadrante')
                    ->whereColumn('rrhh_cuadrante.empleado_id', 'empleados.id')
                    ->where('rrhh_cuadrante.obra_id', $obraId)
                    ->whereBetween('rrhh_cuadrante.fecha', [$d, $h])));
        }

        $empleados = $query->orderBy('apellidos')->orderBy('nombre')->limit(500)->get();
        $ids = $empleados->pluck('id');

        $asignaciones = AsignacionTurno::whereIn('empleado_id', $ids)
            ->whereBetween('fecha', [$d, $h])
            ->with('obra:id,nombre')
            ->get()
            ->groupBy('empleado_id');

        $ausencias = Ausencia::whereIn('empleado_id', $ids)
            ->solapadas($d, $h)
            ->get(['id', 'empleado_id', 'rrhh_tipo_ausencia_id', 'fecha_inicio', 'fecha_fin'])
            ->groupBy('empleado_id');

        $festivos = $this->festivosEntre($desde, $hasta);
        $dias = [];
        for ($dia = $desde; $dia->lte($hasta); $dia = $dia->addDay()) {
            $dias[] = [
                'fecha' => $dia->toDateString(),
                'dia' => $dia->day,
                'mes' => $dia->month,
                'semana' => $dia->dayOfWeekIso,
                'festivo' => $festivos[$dia->toDateString()] ?? null,
            ];
        }

        return response()->json([
            'desde' => $d,
            'hasta' => $h,
            'dias' => $dias,
            'turnos' => Turno::orderBy('orden')->orderBy('nombre')->get(),
            'tipos_ausencia' => RrhhTipoAusencia::orderBy('orden')->get(['id', 'nombre', 'color']),
            'empleados' => $empleados->map(fn (Empleado $e) => [
                'id' => $e->id,
                'nombre' => $e->apellidos.', '.$e->nombre,
                'nombre_completo' => $e->nombre_completo,
                'puesto' => $e->puesto,
                'horas_semanales' => $e->horas_semanales !== null ? (float) $e->horas_semanales : null,
                'obras' => $e->obras->map->only(['id', 'nombre'])->values(),
                'periodos' => $e->periodos->map(fn ($p) => [
                    'desde' => $p->fecha_alta->toDateString(),
                    'hasta' => $p->fecha_baja?->toDateString(),
                ])->values(),
                'asignaciones' => ($asignaciones[$e->id] ?? collect())->map(fn (AsignacionTurno $a) => [
                    'id' => $a->id,
                    'fecha' => $a->fecha->toDateString(),
                    'turno_id' => $a->rrhh_turno_id,
                    'obra' => $a->obra ? ['id' => $a->obra->id, 'nombre' => $a->obra->nombre] : null,
                    'observaciones' => $a->observaciones,
                ])->values(),
                'ausencias' => ($ausencias[$e->id] ?? collect())->map(fn (Ausencia $a) => [
                    'tipo_id' => $a->rrhh_tipo_ausencia_id,
                    'desde' => $a->fecha_inicio->toDateString(),
                    'hasta' => $a->fecha_fin?->toDateString(),
                ])->values(),
            ])->values(),
        ]);
    }

    /** Asigna un turno (y obra) a varios empleados en un rango de fechas. */
    public function asignar(Request $request)
    {
        $datos = $request->validate([
            'empleado_ids' => ['required', 'array', 'min:1', 'max:500'],
            'empleado_ids.*' => ['integer', 'distinct'],
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
            'dias_semana' => ['nullable', 'array'],
            'dias_semana.*' => ['integer', 'between:1,7'],
            'rrhh_turno_id' => ['required', 'integer', Rule::exists('rrhh_turnos', 'id')->where('activo', true)],
            'obra_id' => ['nullable', 'integer', Rule::exists('obras', 'id')],
            'observaciones' => ['nullable', 'string', 'max:255'],
            'sobrescribir' => ['boolean'],
            'incluir_festivos' => ['boolean'],
        ], [
            'empleado_ids.required' => 'Elige al menos un empleado.',
            'rrhh_turno_id.exists' => 'Elige un turno activo.',
        ]);

        $desde = CarbonImmutable::parse($datos['desde'])->startOfDay();
        $hasta = CarbonImmutable::parse($datos['hasta'])->startOfDay();
        $this->limitarRango($desde, $hasta);

        $dias = [];
        $semana = $datos['dias_semana'] ?? [1, 2, 3, 4, 5, 6, 7];
        for ($d = $desde; $d->lte($hasta); $d = $d->addDay()) {
            if (in_array($d->dayOfWeekIso, $semana)) {
                $dias[] = $d;
            }
        }

        $valores = [
            'rrhh_turno_id' => $datos['rrhh_turno_id'],
            'obra_id' => $datos['obra_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
        ];

        return response()->json($this->aplicar(
            $datos['empleado_ids'],
            collect($dias)->map(fn ($d) => ['fecha' => $d] + $valores),
            (bool) ($datos['sobrescribir'] ?? false),
            (bool) ($datos['incluir_festivos'] ?? false),
        ));
    }

    /** Copia las asignaciones de un rango a otro que empieza en `destino`. */
    public function copiar(Request $request)
    {
        $datos = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
            'destino' => ['required', 'date'],
            'empleado_ids' => ['nullable', 'array'],
            'empleado_ids.*' => ['integer'],
            'sobrescribir' => ['boolean'],
        ]);

        $desde = CarbonImmutable::parse($datos['desde'])->startOfDay();
        $hasta = CarbonImmutable::parse($datos['hasta'])->startOfDay();
        $this->limitarRango($desde, $hasta);
        $desplazamiento = (int) $desde->diffInDays(CarbonImmutable::parse($datos['destino'])->startOfDay(), false);

        if ($desplazamiento === 0) {
            throw ValidationException::withMessages(['destino' => 'El destino debe ser distinto del origen.']);
        }

        $origen = AsignacionTurno::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when(! empty($datos['empleado_ids']), fn ($q) => $q->whereIn('empleado_id', $datos['empleado_ids']))
            ->get();

        if ($origen->isEmpty()) {
            throw ValidationException::withMessages(['desde' => 'No hay turnos asignados en las fechas de origen.']);
        }

        $resultado = ['creadas' => 0, 'actualizadas' => 0, 'omitidas' => []];
        foreach ($origen->groupBy('empleado_id') as $empleadoId => $filas) {
            $parcial = $this->aplicar(
                [$empleadoId],
                $filas->map(fn (AsignacionTurno $a) => [
                    'fecha' => CarbonImmutable::parse($a->fecha)->addDays($desplazamiento),
                    'rrhh_turno_id' => $a->rrhh_turno_id,
                    'obra_id' => $a->obra_id,
                    'observaciones' => $a->observaciones,
                ]),
                (bool) ($datos['sobrescribir'] ?? false),
                true,
                false,
            );
            $resultado['creadas'] += $parcial['creadas'];
            $resultado['actualizadas'] += $parcial['actualizadas'];
            foreach ($parcial['omitidas'] as $motivo => $n) {
                $resultado['omitidas'][$motivo] = ($resultado['omitidas'][$motivo] ?? 0) + $n;
            }
        }
        $resultado['message'] = $this->mensaje($resultado);

        return response()->json($resultado);
    }

    /** Quita las asignaciones de varios empleados en un rango. */
    public function eliminar(Request $request)
    {
        $datos = $request->validate([
            'empleado_ids' => ['required', 'array', 'min:1'],
            'empleado_ids.*' => ['integer'],
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $n = AsignacionTurno::whereIn('empleado_id', $datos['empleado_ids'])
            ->whereBetween('fecha', [
                CarbonImmutable::parse($datos['desde'])->toDateString(),
                CarbonImmutable::parse($datos['hasta'])->toDateString(),
            ])
            ->delete();

        return response()->json([
            'message' => $n === 1 ? 'Se quitó 1 turno' : "Se quitaron {$n} turnos",
            'eliminadas' => $n,
        ]);
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    /**
     * Crea o actualiza asignaciones saltando los días no válidos.
     *
     * @param  Collection<int, array{fecha: CarbonImmutable, rrhh_turno_id: int, obra_id: ?int, observaciones: ?string}>  $filas
     */
    private function aplicar(array $empleadoIds, Collection $filas, bool $sobrescribir, bool $incluirFestivos, bool $conMensaje = true): array
    {
        $resultado = ['creadas' => 0, 'actualizadas' => 0, 'omitidas' => []];
        $omitir = function (string $motivo) use (&$resultado) {
            $resultado['omitidas'][$motivo] = ($resultado['omitidas'][$motivo] ?? 0) + 1;
        };

        if ($filas->isEmpty()) {
            $resultado['message'] = 'No hay días que coincidan con lo elegido.';

            return $resultado;
        }

        $min = $filas->min('fecha')->toDateString();
        $max = $filas->max('fecha')->toDateString();

        $empleados = Empleado::whereIn('id', $empleadoIds)->with('periodos')->get();
        if ($empleados->count() !== count(array_unique($empleadoIds))) {
            throw ValidationException::withMessages(['empleado_ids' => 'Algún empleado no existe.']);
        }

        $ausencias = Ausencia::whereIn('empleado_id', $empleadoIds)->solapadas($min, $max)->get()->groupBy('empleado_id');
        $existentes = AsignacionTurno::whereIn('empleado_id', $empleadoIds)
            ->whereBetween('fecha', [$min, $max])
            ->get()
            ->keyBy(fn ($a) => $a->empleado_id.'|'.$a->fecha->toDateString());
        $festivos = $this->festivosEntre(CarbonImmutable::parse($min), CarbonImmutable::parse($max));
        $usuario = auth()->id();

        DB::transaction(function () use ($empleados, $filas, $ausencias, $existentes, $festivos, $sobrescribir, $incluirFestivos, $usuario, $omitir, &$resultado) {
            foreach ($empleados as $e) {
                foreach ($filas as $fila) {
                    $fecha = $fila['fecha']->toDateString();

                    $deAlta = $e->periodos->contains(fn ($p) => $p->fecha_alta->toDateString() <= $fecha
                        && (! $p->fecha_baja || $p->fecha_baja->toDateString() >= $fecha));
                    if (! $deAlta) {
                        $omitir('no_alta');

                        continue;
                    }

                    $ausente = ($ausencias[$e->id] ?? collect())->contains(fn ($a) => $a->fecha_inicio->toDateString() <= $fecha
                        && (! $a->fecha_fin || $a->fecha_fin->toDateString() >= $fecha));
                    if ($ausente) {
                        $omitir('ausencia');

                        continue;
                    }

                    if (! $incluirFestivos && isset($festivos[$fecha])) {
                        $omitir('festivo');

                        continue;
                    }

                    $valores = [
                        'rrhh_turno_id' => $fila['rrhh_turno_id'],
                        'obra_id' => $fila['obra_id'],
                        'observaciones' => $fila['observaciones'],
                        'usuario_id' => $usuario,
                    ];

                    if ($actual = $existentes->get($e->id.'|'.$fecha)) {
                        if (! $sobrescribir) {
                            $omitir('ya_asignado');

                            continue;
                        }
                        $actual->update($valores);
                        $resultado['actualizadas']++;
                    } else {
                        AsignacionTurno::create($valores + ['empleado_id' => $e->id, 'fecha' => $fecha]);
                        $resultado['creadas']++;
                    }
                }
            }
        });

        if ($conMensaje) {
            $resultado['message'] = $this->mensaje($resultado);
        }

        return $resultado;
    }

    private function mensaje(array $r): string
    {
        $partes = [];
        if ($r['creadas']) {
            $partes[] = $r['creadas'] === 1 ? '1 turno asignado' : "{$r['creadas']} turnos asignados";
        }
        if ($r['actualizadas']) {
            $partes[] = $r['actualizadas'] === 1 ? '1 cambiado' : "{$r['actualizadas']} cambiados";
        }

        $motivos = [
            'ausencia' => 'con ausencia',
            'festivo' => 'festivos',
            'no_alta' => 'sin estar de alta',
            'ya_asignado' => 'que ya tenían turno',
        ];
        $omitidos = collect($r['omitidas'])->map(fn ($n, $m) => "{$n} {$motivos[$m]}")->implode(', ');

        $texto = $partes ? ucfirst(implode(', ', $partes)) : 'No se asignó ningún turno';

        return $omitidos ? "{$texto}. Días saltados: {$omitidos}." : "{$texto}.";
    }

    /** @return array<string, string> */
    private function festivosEntre(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $festivos = [];
        for ($anio = $desde->year; $anio <= $hasta->year; $anio++) {
            $festivos += $this->calendario->festivosDelAnio($anio);
        }

        return $festivos;
    }

    private function limitarRango(CarbonImmutable $desde, CarbonImmutable $hasta): void
    {
        if ($desde->diffInDays($hasta) >= self::MAX_DIAS_ASIGNAR) {
            throw ValidationException::withMessages(['hasta' => 'Como máximo '.self::MAX_DIAS_ASIGNAR.' días de una vez.']);
        }
    }
}
