<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\RrhhTipoAusencia;
use App\Services\Rrhh\CalendarioLaboral;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Calendario mensual del equipo: empleados que estuvieron de alta en el mes,
 * sus ausencias, fines de semana y festivos.
 */
class CalendarioController extends Controller
{
    public function __construct(
        private readonly CalendarioLaboral $calendario,
    ) {}

    public function index(Request $request)
    {
        $anio = (int) $request->input('anio', date('Y'));
        $mes = (int) $request->input('mes', date('n'));
        if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
            [$anio, $mes] = [(int) date('Y'), (int) date('n')];
        }

        $desde = CarbonImmutable::create($anio, $mes, 1);
        $hasta = $desde->endOfMonth()->startOfDay();
        [$d, $h] = [$desde->toDateString(), $hasta->toDateString()];

        $query = Empleado::query()
            ->whereHas('periodos', fn ($q) => $q->where('fecha_alta', '<=', $h)
                ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $d)))
            ->with(['periodos' => fn ($q) => $q->where('fecha_alta', '<=', $h)
                ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $d))]);

        if ($busqueda = trim((string) $request->input('search'))) {
            $like = '%'.addcslashes($busqueda, '%_\\').'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $like)
                ->orWhere('apellidos', 'like', $like)
                ->orWhere('dni', 'like', $like)
                ->orWhere('puesto', 'like', $like));
        }

        if ($request->filled('obra_id')) {
            $query->whereHas('obras', fn ($q) => $q->where('obras.id', (int) $request->input('obra_id')));
        }

        $empleados = $query->orderBy('apellidos')->orderBy('nombre')->limit(500)->get();

        $ausencias = Ausencia::whereIn('empleado_id', $empleados->pluck('id'))
            ->solapadas($d, $h)
            ->get(['id', 'empleado_id', 'rrhh_tipo_ausencia_id', 'fecha_inicio', 'fecha_fin'])
            ->groupBy('empleado_id');

        $festivos = $this->calendario->festivosDelAnio($anio);
        $dias = [];
        for ($dia = $desde; $dia->lte($hasta); $dia = $dia->addDay()) {
            $dias[] = [
                'fecha' => $dia->toDateString(),
                'dia' => $dia->day,
                'semana' => $dia->dayOfWeekIso,
                'festivo' => $festivos[$dia->toDateString()] ?? null,
            ];
        }

        return response()->json([
            'anio' => $anio,
            'mes' => $mes,
            'dias' => $dias,
            'tipos' => RrhhTipoAusencia::orderBy('orden')->orderBy('nombre')->get(),
            'empleados' => $empleados->map(fn (Empleado $e) => [
                'id' => $e->id,
                'nombre' => $e->apellidos.', '.$e->nombre,
                'nombre_completo' => $e->nombre_completo,
                'puesto' => $e->puesto,
                'estado' => $e->estado,
                'periodos' => $e->periodos->map(fn ($p) => [
                    'desde' => $p->fecha_alta->toDateString(),
                    'hasta' => $p->fecha_baja?->toDateString(),
                ])->values(),
                'ausencias' => ($ausencias[$e->id] ?? collect())->map(fn (Ausencia $a) => [
                    'id' => $a->id,
                    'tipo_id' => $a->rrhh_tipo_ausencia_id,
                    'desde' => $a->fecha_inicio->toDateString(),
                    'hasta' => $a->fecha_fin?->toDateString(),
                ])->values(),
            ])->values(),
        ]);
    }
}
