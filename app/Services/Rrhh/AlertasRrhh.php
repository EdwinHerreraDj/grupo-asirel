<?php

namespace App\Services\Rrhh;

use App\Http\Controllers\Api\Rrhh\CursoController;
use App\Models\Anticipo;
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
 * Todo lo de Recursos humanos que requiere atención, en una lista.
 *
 * Cada alerta: categoria, nivel (critico | aviso | info), titulo, detalle y,
 * si es de un empleado, empleado {id, nombre_completo}.
 */
class AlertasRrhh
{
    public const CATEGORIAS = [
        'contratos' => 'Contratos',
        'documentacion' => 'Documentación',
        'formacion' => 'Formación',
        'bajas' => 'Bajas médicas',
        'nominas' => 'Nóminas',
        'anticipos' => 'Anticipos y vales',
        'vacaciones' => 'Vacaciones',
        'cuadrante' => 'Cuadrante',
        'obras' => 'Obras',
        'cumpleanos' => 'Cumpleaños',
    ];

    /** Días de antelación para avisar del fin de contrato. */
    public const DIAS_FIN_CONTRATO = 30;

    /** Días tras los que un anticipo sin descontar se considera antiguo. */
    public const DIAS_ANTICIPO_ANTIGUO = 60;

    private CarbonImmutable $hoy;

    /** @var Collection<int, Empleado> */
    private Collection $activos;

    private array $alertas = [];

    public function __construct(
        private readonly EstadoDocumentacion $documentacion,
        private readonly CalendarioLaboral $calendario,
    ) {}

    /** @return array{alertas: array, totales: array} */
    public function calcular(?CarbonImmutable $hoy = null): array
    {
        $this->hoy = $hoy ?? CarbonImmutable::today();
        $this->alertas = [];
        $this->activos = Empleado::where('estado', Empleado::ESTADO_ACTIVO)
            ->with('obras:obras.id')
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get();

        $this->contratos();
        $this->documentacion();
        $this->formacion();
        $this->bajasMedicas();
        $this->nominas();
        $this->anticipos();
        $this->vacaciones();
        $this->cuadrante();
        $this->sinObra();
        $this->cumpleanos();

        $orden = ['critico' => 0, 'aviso' => 1, 'info' => 2];
        usort($this->alertas, fn ($a, $b) => [$orden[$a['nivel']], $a['categoria']] <=> [$orden[$b['nivel']], $b['categoria']]);

        $niveles = collect($this->alertas)->countBy('nivel');

        return [
            'alertas' => $this->alertas,
            'totales' => [
                'critico' => $niveles['critico'] ?? 0,
                'aviso' => $niveles['aviso'] ?? 0,
                'info' => $niveles['info'] ?? 0,
                'total' => count($this->alertas),
            ],
            'categorias' => self::CATEGORIAS,
        ];
    }

    // -------------------------------------------------------------

    private function contratos(): void
    {
        $limite = $this->hoy->addDays(self::DIAS_FIN_CONTRATO)->toDateString();

        EmpleadoPeriodo::whereNull('fecha_baja')
            ->whereNotNull('fecha_fin_contrato')
            ->where('fecha_fin_contrato', '<=', $limite)
            ->whereIn('empleado_id', $this->activos->pluck('id'))
            ->orderBy('fecha_fin_contrato')
            ->get()
            ->each(function (EmpleadoPeriodo $p) {
                $dias = (int) $this->hoy->diffInDays($p->fecha_fin_contrato, false);
                $fecha = $p->fecha_fin_contrato->format('d/m/Y');

                $dias < 0
                    ? $this->anadir('contratos', 'critico', 'Contrato vencido y sigue de alta', "Terminaba el {$fecha}. Dale de baja o actualiza la fecha de fin.", $p->empleado_id)
                    : $this->anadir('contratos', 'aviso', 'Fin de contrato próximo', $dias === 0 ? "Termina hoy ({$fecha})." : "Termina el {$fecha} (en {$dias} días).", $p->empleado_id);
            });
    }

    private function documentacion(): void
    {
        $docs = $this->documentacion->paraEmpleados($this->activos);

        foreach ($this->activos as $e) {
            $problemas = collect($docs[$e->id] ?? [])->whereIn('estado', EstadoDocumentacion::PROBLEMAS);
            if ($problemas->isEmpty()) {
                continue;
            }

            $graves = $problemas->whereIn('estado', [EstadoDocumentacion::FALTA, EstadoDocumentacion::VENCIDO]);
            $etiquetas = [
                EstadoDocumentacion::FALTA => 'falta',
                EstadoDocumentacion::VENCIDO => 'caducado',
                EstadoDocumentacion::PROXIMO => 'caduca pronto',
                EstadoDocumentacion::SIN_FECHA => 'sin fecha',
            ];

            $this->anadir(
                'documentacion',
                $graves->isNotEmpty() ? 'critico' : 'aviso',
                $problemas->count() === 1 ? '1 documento pendiente' : "{$problemas->count()} documentos pendientes",
                $problemas->map(fn ($p) => "{$p['tipo']} ({$etiquetas[$p['estado']]})")->implode(', '),
                $e->id,
            );
        }
    }

    private function formacion(): void
    {
        CursoController::conEstado(Curso::whereIn('empleado_id', $this->activos->pluck('id'))->get())
            ->whereIn('estado', ['vencido', 'proximo'])
            ->sortBy('fecha_caducidad')
            ->each(fn (Curso $c) => $this->anadir(
                'formacion',
                $c->estado === 'vencido' ? 'critico' : 'aviso',
                $c->estado === 'vencido' ? 'Formación caducada' : 'Formación que caduca pronto',
                "{$c->nombre}: ".($c->estado === 'vencido' ? 'caducó' : 'caduca').' el '.$c->fecha_caducidad->format('d/m/Y').'.',
                $c->empleado_id,
            ));
    }

    private function bajasMedicas(): void
    {
        Ausencia::whereNull('fecha_fin')
            ->whereIn('empleado_id', $this->activos->pluck('id'))
            ->whereHas('tipo', fn ($q) => $q->where('es_baja_medica', true))
            ->with('tipo:id,nombre')
            ->get()
            ->each(function (Ausencia $a) {
                $dias = (int) $a->fecha_inicio->diffInDays($this->hoy) + 1;
                $desde = $a->fecha_inicio->format('d/m/Y');

                // La incapacidad temporal dura como máximo 365 días (prorrogables): avisar antes.
                $nivel = $dias >= 300 ? 'critico' : ($dias > 30 ? 'aviso' : 'info');
                $extra = $dias >= 300 ? ' Se acerca al límite de 365 días de incapacidad temporal.' : '';

                $this->anadir('bajas', $nivel, "{$a->tipo->nombre} abierta", "Desde el {$desde} ({$dias} días).{$extra}", $a->empleado_id);
            });
    }

    private function nominas(): void
    {
        $mes = $this->hoy->subMonthNoOverflow()->startOfMonth();
        [$d, $h] = [$mes->toDateString(), $mes->endOfMonth()->toDateString()];
        $nombreMes = mb_strtolower($mes->locale('es')->translatedFormat('F Y'));

        // Empleados de alta el mes pasado sin nómina mensual.
        $deAlta = Empleado::whereHas('periodos', fn ($q) => $q->where('fecha_alta', '<=', $h)
            ->where(fn ($q) => $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $d)))
            ->pluck('id');
        $conNomina = Nomina::where('anio', $mes->year)->where('mes', $mes->month)->where('tipo', 'mensual')->pluck('empleado_id');
        $sin = $deAlta->diff($conNomina)->count();

        if ($sin > 0) {
            $this->anadir('nominas', 'aviso', "Nóminas de {$nombreMes} sin registrar", $sin === 1 ? '1 empleado no tiene la nómina registrada.' : "{$sin} empleados no tienen la nómina registrada.");
        }

        // Nóminas de meses anteriores al actual pendientes de pago.
        $pendientes = Nomina::where('estado', Nomina::ESTADO_PENDIENTE)
            ->where(fn ($q) => $q->where('anio', '<', $this->hoy->year)
                ->orWhere(fn ($q) => $q->where('anio', $this->hoy->year)->where('mes', '<', $this->hoy->month)))
            ->count();

        if ($pendientes > 0) {
            $this->anadir('nominas', 'critico', 'Nóminas pendientes de pago', $pendientes === 1 ? '1 nómina de meses anteriores sigue sin pagar.' : "{$pendientes} nóminas de meses anteriores siguen sin pagar.");
        }
    }

    private function anticipos(): void
    {
        Anticipo::whereNull('nomina_id')
            ->where('fecha', '<', $this->hoy->subDays(self::DIAS_ANTICIPO_ANTIGUO)->toDateString())
            ->whereIn('empleado_id', $this->activos->pluck('id'))
            ->selectRaw('empleado_id, COUNT(*) as n, SUM(importe) as total, MIN(fecha) as desde')
            ->groupBy('empleado_id')
            ->get()
            ->each(fn ($f) => $this->anadir(
                'anticipos',
                'aviso',
                'Anticipos sin descontar',
                number_format((float) $f->total, 2, ',', '.').' € entregados desde el '.CarbonImmutable::parse($f->desde)->format('d/m/Y').' sin descontar en nómina.',
                $f->empleado_id,
            ));
    }

    /** Desde octubre: vacaciones del año sin disfrutar ni programar. */
    private function vacaciones(): void
    {
        $tipo = RrhhTipoAusencia::where('es_vacaciones', true)->where('activo', true)->whereNotNull('dias_anuales')->first();

        if ($this->hoy->month < 10 || ! $tipo) {
            return;
        }

        foreach ($this->activos as $e) {
            $vacaciones = $this->calendario->saldos($e, $this->hoy->year, collect([$tipo]))[0] ?? null;

            if ($vacaciones && $vacaciones['pendientes'] >= 1) {
                $n = rtrim(rtrim(number_format($vacaciones['pendientes'], 1, ',', ''), '0'), ',');
                $this->anadir('vacaciones', 'info', 'Vacaciones sin disfrutar', "Le quedan {$n} días de {$this->hoy->year} por disfrutar o programar.", $e->id);
            }
        }
    }

    /** Si se usa el cuadrante: quién no tiene turno la próxima semana. */
    private function cuadrante(): void
    {
        $lunes = $this->hoy->startOfWeek()->addWeek();
        $viernes = $lunes->addDays(4);

        if (! AsignacionTurno::where('fecha', '>=', $this->hoy->subDays(30)->toDateString())->exists()) {
            return;
        }

        $conTurno = AsignacionTurno::whereBetween('fecha', [$lunes->toDateString(), $viernes->toDateString()])
            ->distinct()
            ->pluck('empleado_id');
        $sin = $this->activos->pluck('id')->diff($conTurno)->count();

        if ($sin > 0) {
            $this->anadir('cuadrante', 'info', 'Cuadrante de la próxima semana incompleto', $sin === 1 ? '1 empleado sin turno asignado.' : "{$sin} empleados sin turno asignado.");
        }
    }

    private function sinObra(): void
    {
        $sin = $this->activos->filter(fn ($e) => $e->obras->isEmpty())->count();

        if ($sin > 0) {
            $this->anadir('obras', 'info', 'Empleados sin obra asignada', $sin === 1 ? '1 empleado de alta no tiene obra.' : "{$sin} empleados de alta no tienen obra.");
        }
    }

    private function cumpleanos(): void
    {
        foreach ($this->activos as $e) {
            if (! $e->fecha_nacimiento) {
                continue;
            }

            $cumple = $e->fecha_nacimiento->copy()->setYear($this->hoy->year);
            if ($cumple->lt($this->hoy)) {
                $cumple = $cumple->addYear();
            }
            $dias = (int) $this->hoy->diffInDays($cumple);

            if ($dias <= 7) {
                $edad = $cumple->year - $e->fecha_nacimiento->year;
                $this->anadir('cumpleanos', 'info', $dias === 0 ? 'Cumple años hoy' : 'Cumpleaños esta semana', "{$edad} años el ".$cumple->format('d/m').'.', $e->id);
            }
        }
    }

    private function anadir(string $categoria, string $nivel, string $titulo, string $detalle, ?int $empleadoId = null): void
    {
        $empleado = $empleadoId ? $this->activos->firstWhere('id', $empleadoId) : null;

        $this->alertas[] = [
            'categoria' => $categoria,
            'nivel' => $nivel,
            'titulo' => $titulo,
            'detalle' => $detalle,
            'empleado' => $empleado ? ['id' => $empleado->id, 'nombre_completo' => $empleado->nombre_completo] : null,
        ];
    }
}
