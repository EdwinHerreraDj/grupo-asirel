<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Anticipo;
use App\Models\Empleado;
use App\Models\Nomina;
use App\Services\Rrhh\AdjuntosRrhh;
use App\Services\Rrhh\CarpetasEmpleados;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Nóminas: se registran los importes que da la gestoría, el PDF y si está
 * pagada. Los anticipos y vales pendientes se descuentan eligiéndolos al
 * registrar la nómina.
 */
class NominaController extends Controller
{
    private const CON = ['anticiposDescontados:id,nomina_id,tipo,fecha,importe,concepto', 'archivo:id,nombre'];

    public function __construct(
        private readonly AdjuntosRrhh $adjuntos,
    ) {}

    /** Nóminas del año de un empleado, con totales y anticipos pendientes. */
    public function porEmpleado(Request $request, Empleado $empleado)
    {
        $anio = $this->anio($request);

        $nominas = Nomina::where('empleado_id', $empleado->id)
            ->where('anio', $anio)
            ->with(self::CON)
            ->orderByDesc('mes')
            ->orderBy('tipo')
            ->get();

        $primerAlta = $empleado->periodos()->min('fecha_alta');
        $desde = $primerAlta ? (int) substr($primerAlta, 0, 4) : (int) date('Y');

        return response()->json([
            'anio' => $anio,
            'anios' => range((int) date('Y'), min($desde, $anio)),
            'nominas' => $nominas,
            'totales' => $this->totales($nominas),
            'anticipos_pendientes' => $this->anticiposPendientes($empleado->id),
            'tipos' => Nomina::TIPOS,
        ]);
    }

    /** Vista del mes: empleados de alta ese mes y sus nóminas. */
    public function mes(Request $request)
    {
        $anio = $this->anio($request);
        $mes = (int) $request->input('mes', date('n'));
        $mes = $mes >= 1 && $mes <= 12 ? $mes : (int) date('n');

        [$desde, $hasta] = $this->limitesMes($anio, $mes);

        $query = Empleado::query()
            ->whereHas('periodos', fn ($q) => $q->where('fecha_alta', '<=', $hasta)
                ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $desde)))
            ->with(['nominas' => fn ($q) => $q->where('anio', $anio)->where('mes', $mes)->with(self::CON)]);

        if ($busqueda = trim((string) $request->input('search'))) {
            $like = '%'.addcslashes($busqueda, '%_\\').'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $like)
                ->orWhere('apellidos', 'like', $like)
                ->orWhere('dni', 'like', $like));
        }
        if ($request->filled('obra_id')) {
            $query->whereHas('obras', fn ($q) => $q->where('obras.id', (int) $request->input('obra_id')));
        }

        $empleados = $query->orderBy('apellidos')->orderBy('nombre')->limit(500)->get();

        $pendientes = Anticipo::whereIn('empleado_id', $empleados->pluck('id'))
            ->whereNull('nomina_id')
            ->groupBy('empleado_id')
            ->selectRaw('empleado_id, SUM(importe) as total')
            ->pluck('total', 'empleado_id');

        $todas = $empleados->flatMap->nominas;
        $sinMensual = $empleados->filter(fn ($e) => ! $e->nominas->contains('tipo', 'mensual'))->count();

        return response()->json([
            'anio' => $anio,
            'mes' => $mes,
            'empleados' => $empleados->map(fn (Empleado $e) => [
                'id' => $e->id,
                'nombre' => $e->apellidos.', '.$e->nombre,
                'nombre_completo' => $e->nombre_completo,
                'dni' => $e->dni,
                'puesto' => $e->puesto,
                'nominas' => $e->nominas->values(),
                'anticipos_pendientes' => round((float) ($pendientes[$e->id] ?? 0), 2),
            ])->values(),
            'totales' => $this->totales($todas) + [
                'empleados' => $empleados->count(),
                'sin_nomina' => $sinMensual,
            ],
            'tipos' => Nomina::TIPOS,
        ]);
    }

    public function store(Request $request, Empleado $empleado)
    {
        [$datos, $anticipos] = $this->validar($request, $empleado);

        $nomina = DB::transaction(function () use ($request, $empleado, $datos, $anticipos) {
            $nomina = Nomina::create($datos + [
                'empleado_id' => $empleado->id,
                'usuario_id' => $request->user()->id,
                'anticipos' => round($anticipos->sum('importe'), 2),
            ]);
            Anticipo::whereIn('id', $anticipos->pluck('id'))->update(['nomina_id' => $nomina->id]);
            $this->guardarArchivo($request, $empleado, $nomina);

            return $nomina;
        });

        return response()->json([
            'message' => 'Nómina registrada',
            'nomina' => $nomina->fresh(self::CON),
            'aviso' => $this->avisoNeto($nomina->fresh()),
        ], 201);
    }

    public function update(Request $request, Nomina $nomina)
    {
        $empleado = $nomina->empleado;
        [$datos, $anticipos] = $this->validar($request, $empleado, $nomina);

        DB::transaction(function () use ($request, $empleado, $nomina, $datos, $anticipos) {
            // Los que se quitan vuelven a quedar pendientes.
            Anticipo::where('nomina_id', $nomina->id)
                ->whereNotIn('id', $anticipos->pluck('id'))
                ->update(['nomina_id' => null]);
            Anticipo::whereIn('id', $anticipos->pluck('id'))->update(['nomina_id' => $nomina->id]);

            $nomina->update($datos + ['anticipos' => round($anticipos->sum('importe'), 2)]);
            $this->guardarArchivo($request, $empleado, $nomina);
        });

        return response()->json([
            'message' => 'Nómina actualizada',
            'nomina' => $nomina->fresh(self::CON),
            'aviso' => $this->avisoNeto($nomina->fresh()),
        ]);
    }

    /** Sus anticipos vuelven a pendientes (FK); el PDF se queda en el Drive. */
    public function destroy(Nomina $nomina)
    {
        $nomina->delete();

        return response()->json(['message' => 'Nómina eliminada']);
    }

    /** Marca como pagadas varias nóminas pendientes a la vez. */
    public function marcarPagadas(Request $request)
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
            'fecha_pago' => ['required', 'date'],
        ]);

        $n = Nomina::whereIn('id', $datos['ids'])
            ->where('estado', Nomina::ESTADO_PENDIENTE)
            ->update(['estado' => Nomina::ESTADO_PAGADA, 'fecha_pago' => $datos['fecha_pago']]);

        return response()->json([
            'message' => $n === 1 ? '1 nómina marcada como pagada' : "{$n} nóminas marcadas como pagadas",
            'actualizadas' => $n,
        ]);
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    /** @return array{0: array, 1: \Illuminate\Support\Collection} */
    private function validar(Request $request, Empleado $empleado, ?Nomina $actual = null): array
    {
        $dinero = ['nullable', 'numeric', 'min:0', 'max:9999999'];

        $datos = $request->validate([
            'anio' => ['required', 'integer', 'between:2000,2100'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'tipo' => ['required', Rule::in(array_keys(Nomina::TIPOS))],
            'bruto' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'irpf' => $dinero,
            'seguridad_social' => $dinero,
            'otras_deducciones' => $dinero,
            'neto' => ['required', 'numeric', 'min:-9999999', 'max:9999999'],
            'coste_empresa' => $dinero,
            'estado' => ['required', Rule::in([Nomina::ESTADO_PENDIENTE, Nomina::ESTADO_PAGADA])],
            'fecha_pago' => ['nullable', 'required_if:estado,pagada', 'date'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'anticipo_ids' => ['nullable', 'array'],
            'anticipo_ids.*' => ['integer', 'distinct'],
            'archivo' => ['nullable', 'file', 'max:51200'],
        ], [
            'fecha_pago.required_if' => 'Indica la fecha de pago.',
            'archivo.max' => 'El PDF no puede superar 50 MB.',
        ]);

        $ids = $datos['anticipo_ids'] ?? [];
        unset($datos['anticipo_ids'], $datos['archivo']);

        foreach (['irpf', 'seguridad_social', 'otras_deducciones'] as $campo) {
            $datos[$campo] = $datos[$campo] ?? 0;
        }
        if ($datos['estado'] === Nomina::ESTADO_PENDIENTE) {
            $datos['fecha_pago'] = null;
        }

        [$desde, $hasta] = $this->limitesMes($datos['anio'], $datos['mes']);
        if (! $empleado->estuvoDeAltaEntre($desde, $hasta)) {
            throw ValidationException::withMessages(['mes' => 'El empleado no estuvo de alta ese mes.']);
        }

        $repetida = Nomina::where('empleado_id', $empleado->id)
            ->where('anio', $datos['anio'])
            ->where('mes', $datos['mes'])
            ->where('tipo', $datos['tipo'])
            ->when($actual, fn ($q) => $q->where('id', '!=', $actual->id))
            ->exists();
        if ($repetida) {
            $tipo = mb_strtolower(Nomina::TIPOS[$datos['tipo']]);
            throw ValidationException::withMessages(['mes' => "Ya hay una nómina ({$tipo}) de {$datos['mes']}/{$datos['anio']}."]);
        }

        $anticipos = Anticipo::whereIn('id', $ids)
            ->where('empleado_id', $empleado->id)
            ->where(fn ($q) => $q->whereNull('nomina_id')->when($actual, fn ($q) => $q->orWhere('nomina_id', $actual->id)))
            ->get(['id', 'importe']);
        if ($anticipos->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'anticipo_ids' => 'Algún anticipo no es de este empleado o ya se descontó en otra nómina.',
            ]);
        }

        return [$datos, $anticipos];
    }

    private function guardarArchivo(Request $request, Empleado $empleado, Nomina $nomina): void
    {
        if (! $request->hasFile('archivo')) {
            return;
        }

        $prefijo = sprintf('%04d-%02d %s', $nomina->anio, $nomina->mes, 'Nómina '.mb_strtolower(Nomina::TIPOS[$nomina->tipo]));
        $file = $this->adjuntos->guardar($empleado, $request->file('archivo'), CarpetasEmpleados::SISTEMA_NOMINAS, $prefijo);
        $nomina->update(['file_id' => $file->id]);
    }

    /** Aviso (no bloquea) si el neto no cuadra con bruto menos deducciones. */
    private function avisoNeto(Nomina $n): ?string
    {
        $calculado = round($n->bruto - $n->irpf - $n->seguridad_social - $n->anticipos - $n->otras_deducciones, 2);

        if (abs($calculado - (float) $n->neto) < 0.01) {
            return null;
        }

        return 'El neto ('.number_format((float) $n->neto, 2, ',', '.').' €) no coincide con bruto menos deducciones ('
            .number_format($calculado, 2, ',', '.').' €). Revísalo si no es intencionado.';
    }

    private function totales($nominas): array
    {
        $suma = fn ($campo) => round($nominas->sum(fn ($n) => (float) $n->{$campo}), 2);

        return [
            'bruto' => $suma('bruto'),
            'irpf' => $suma('irpf'),
            'seguridad_social' => $suma('seguridad_social'),
            'anticipos' => $suma('anticipos'),
            'neto' => $suma('neto'),
            'coste_empresa' => $suma('coste_empresa'),
            'registradas' => $nominas->count(),
            'pagadas' => $nominas->where('estado', Nomina::ESTADO_PAGADA)->count(),
            'pendientes_pago' => $nominas->where('estado', Nomina::ESTADO_PENDIENTE)->count(),
        ];
    }

    private function anticiposPendientes(int $empleadoId)
    {
        return Anticipo::where('empleado_id', $empleadoId)
            ->whereNull('nomina_id')
            ->orderBy('fecha')
            ->get(['id', 'tipo', 'fecha', 'importe', 'concepto']);
    }

    /** @return array{0: string, 1: string} */
    private function limitesMes(int $anio, int $mes): array
    {
        $inicio = CarbonImmutable::create($anio, $mes, 1);

        return [$inicio->toDateString(), $inicio->endOfMonth()->toDateString()];
    }

    private function anio(Request $request): int
    {
        $anio = (int) $request->input('anio', date('Y'));

        return $anio >= 2000 && $anio <= 2100 ? $anio : (int) date('Y');
    }
}
