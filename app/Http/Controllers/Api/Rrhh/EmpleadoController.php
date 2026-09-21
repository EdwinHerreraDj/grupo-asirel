<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Ausencia;
use App\Models\Curso;
use App\Models\Empleado;
use App\Models\Folder;
use App\Models\Obra;
use App\Models\RrhhTipoAusencia;
use App\Rules\DniNie;
use App\Rules\Iban;
use App\Services\Rrhh\CarpetasEmpleados;
use App\Services\Rrhh\EstadoDocumentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Empleados: listado, alta, ficha, edición, baja, reingreso y
 * documentación pendiente. Solo admin y super_admin (middleware en rutas).
 */
class EmpleadoController extends Controller
{
    /** Obras del empleado (columnas cualificadas: la pivote también tiene id). */
    private const OBRAS = 'obras:obras.id,obras.nombre,obras.estado';

    public function __construct(
        private readonly CarpetasEmpleados $carpetas,
        private readonly EstadoDocumentacion $documentacion,
    ) {}

    public function index(Request $request)
    {
        $query = Empleado::query()->with([self::OBRAS, 'periodoActual']);

        if ($busqueda = trim((string) $request->input('search'))) {
            $like = '%'.addcslashes($busqueda, '%_\\').'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $like)
                ->orWhere('apellidos', 'like', $like)
                ->orWhere('dni', 'like', $like)
                ->orWhere('puesto', 'like', $like));
        }

        if (in_array($request->input('estado'), [Empleado::ESTADO_ACTIVO, Empleado::ESTADO_BAJA], true)) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('obra_id')) {
            $query->whereHas('obras', fn ($q) => $q->where('obras.id', (int) $request->input('obra_id')));
        }

        $empleados = $query->orderBy('apellidos')->orderBy('nombre')->paginate(15);

        $docs = $this->documentacion->paraEmpleados($empleados->getCollection());

        // Quién está ausente hoy (vacaciones, baja médica…).
        $hoy = today()->toDateString();
        $ausentesHoy = Ausencia::whereIn('empleado_id', $empleados->getCollection()->pluck('id'))
            ->solapadas($hoy, $hoy)
            ->with('tipo:id,nombre,color')
            ->get()
            ->keyBy('empleado_id');

        $empleados->getCollection()->each(function (Empleado $empleado) use ($docs, $ausentesHoy) {
            $ausencia = $ausentesHoy->get($empleado->id);
            $empleado->setAttribute('ausencia_hoy', $ausencia ? [
                'tipo' => $ausencia->tipo->nombre,
                'color' => $ausencia->tipo->color,
                'hasta' => $ausencia->fecha_fin?->toDateString(),
            ] : null);
            $empleado->makeHidden('iban');
            $empleado->setAttribute(
                'documentacion',
                $empleado->estaActivo() ? EstadoDocumentacion::resumen($docs[$empleado->id] ?? []) : null,
            );
        });

        $payload = $empleados->toArray();
        $payload['stats'] = [
            'activos' => Empleado::where('estado', Empleado::ESTADO_ACTIVO)->count(),
            'bajas' => Empleado::where('estado', Empleado::ESTADO_BAJA)->count(),
        ];
        $payload['opciones'] = $this->opciones();

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $this->normalizar($request);

        $datos = $request->validate(
            $this->reglas() + [
                'fecha_alta' => ['required', 'date'],
                'fecha_fin_contrato' => ['nullable', 'date', 'after_or_equal:fecha_alta'],
            ],
            $this->mensajes(),
        );

        $empleado = DB::transaction(function () use ($datos) {
            $empleado = Empleado::create(Arr::except($datos, ['fecha_alta', 'fecha_fin_contrato', 'obra_ids']) + [
                'estado' => Empleado::ESTADO_ACTIVO,
            ]);
            $empleado->obras()->sync($datos['obra_ids'] ?? []);

            $empleado->periodos()->create([
                'fecha_alta' => $datos['fecha_alta'],
                'tipo_contrato' => $datos['tipo_contrato'] ?? null,
                'fecha_fin_contrato' => $datos['fecha_fin_contrato'] ?? null,
            ]);

            $this->carpetas->asegurarCarpeta($empleado);

            return $empleado;
        });

        return response()->json(
            ['message' => 'Empleado dado de alta'] + $this->ficha($empleado->fresh()),
            201,
        );
    }

    public function show(Empleado $empleado)
    {
        // Idempotente: completa carpeta y apartados si falta alguno.
        $this->carpetas->asegurarCarpeta($empleado);

        return response()->json($this->ficha($empleado->fresh()));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $this->normalizar($request);

        $abierto = $empleado->periodos()->whereNull('fecha_baja')->orderByDesc('fecha_alta')->first();

        $datos = $request->validate(
            $this->reglas($empleado) + [
                'fecha_fin_contrato' => array_filter([
                    'nullable',
                    'date',
                    $abierto ? 'after_or_equal:'.$abierto->fecha_alta->toDateString() : null,
                ]),
            ],
            $this->mensajes() + ['fecha_fin_contrato.after_or_equal' => 'El fin de contrato no puede ser anterior al alta.'],
        );

        DB::transaction(function () use ($empleado, $datos, $abierto) {
            $empleado->update(Arr::except($datos, ['obra_ids', 'fecha_fin_contrato']));
            // El fin de contrato previsto es del periodo de alta en curso.
            if ($abierto && array_key_exists('fecha_fin_contrato', $datos)) {
                $abierto->update([
                    'fecha_fin_contrato' => $datos['fecha_fin_contrato'],
                    'tipo_contrato' => $datos['tipo_contrato'] ?? $abierto->tipo_contrato,
                ]);
            }
            if (array_key_exists('obra_ids', $datos)) {
                $empleado->obras()->sync($datos['obra_ids'] ?? []);
            }
            $this->carpetas->renombrarCarpeta($empleado);
            $this->carpetas->asegurarCarpeta($empleado);
        });

        return response()->json(['message' => 'Ficha actualizada'] + $this->ficha($empleado->fresh()));
    }

    public function baja(Request $request, Empleado $empleado)
    {
        if (! $empleado->estaActivo()) {
            return response()->json(['message' => 'El empleado ya está de baja.'], 422);
        }

        $periodo = $empleado->periodoActual;

        $datos = $request->validate([
            'fecha_baja' => array_filter([
                'required',
                'date',
                $periodo ? 'after_or_equal:'.$periodo->fecha_alta->toDateString() : null,
            ]),
            'motivo_baja' => ['required', Rule::in(array_keys(Empleado::MOTIVOS_BAJA))],
            'observaciones_baja' => ['nullable', 'string', 'max:2000'],
        ], [
            'fecha_baja.after_or_equal' => 'La fecha de baja no puede ser anterior a la de alta.',
        ]);

        // Las ausencias no pueden empezar después de la baja.
        $posteriores = Ausencia::where('empleado_id', $empleado->id)
            ->where('fecha_inicio', '>', $datos['fecha_baja'])
            ->with('tipo:id,nombre')
            ->orderBy('fecha_inicio')
            ->get();

        if ($posteriores->isNotEmpty()) {
            $lista = $posteriores->map(fn ($a) => $a->tipo->nombre.' ('.$a->fecha_inicio->format('d/m/Y').')')->implode(', ');
            throw ValidationException::withMessages([
                'fecha_baja' => "Tiene ausencias posteriores a la fecha de baja: {$lista}. Elimínalas o cámbialas antes.",
            ]);
        }

        DB::transaction(function () use ($empleado, $periodo, $datos) {
            // Las ausencias abiertas o que pasan de la baja terminan ese día.
            Ausencia::where('empleado_id', $empleado->id)
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>', $datos['fecha_baja']))
                ->update(['fecha_fin' => $datos['fecha_baja']]);

            if ($periodo && ! $periodo->fecha_baja) {
                $periodo->update($datos);
            } else {
                $empleado->periodos()->create(['fecha_alta' => $datos['fecha_baja']] + $datos);
            }

            $empleado->update(['estado' => Empleado::ESTADO_BAJA]);
            $this->carpetas->moverSegunEstado($empleado);
        });

        return response()->json(['message' => 'Baja registrada'] + $this->ficha($empleado->fresh()));
    }

    public function reingreso(Request $request, Empleado $empleado)
    {
        if ($empleado->estaActivo()) {
            return response()->json(['message' => 'El empleado ya está de alta.'], 422);
        }

        $ultimo = $empleado->periodoActual;

        $datos = $request->validate([
            'fecha_alta' => array_filter([
                'required',
                'date',
                $ultimo?->fecha_baja ? 'after:'.$ultimo->fecha_baja->toDateString() : null,
            ]),
            'tipo_contrato' => ['nullable', Rule::in(array_keys(Empleado::TIPOS_CONTRATO))],
            'fecha_fin_contrato' => ['nullable', 'date', 'after_or_equal:fecha_alta'],
        ], [
            'fecha_alta.after' => 'La nueva fecha de alta debe ser posterior a la última baja.',
            'fecha_fin_contrato.after_or_equal' => 'El fin de contrato no puede ser anterior al alta.',
        ]);

        DB::transaction(function () use ($empleado, $datos) {
            $empleado->periodos()->create([
                'fecha_alta' => $datos['fecha_alta'],
                'tipo_contrato' => $datos['tipo_contrato'] ?? $empleado->tipo_contrato,
                'fecha_fin_contrato' => $datos['fecha_fin_contrato'] ?? null,
            ]);

            $empleado->update(array_filter([
                'estado' => Empleado::ESTADO_ACTIVO,
                'tipo_contrato' => $datos['tipo_contrato'] ?? null,
            ]));

            $this->carpetas->moverSegunEstado($empleado);
        });

        return response()->json(['message' => 'Reingreso registrado'] + $this->ficha($empleado->fresh()));
    }

    /** Empleados de alta con documentos que faltan, caducan o sin fecha. */
    public function pendientes()
    {
        $empleados = Empleado::where('estado', Empleado::ESTADO_ACTIVO)
            ->with(self::OBRAS)
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->get();

        $docs = $this->documentacion->paraEmpleados($empleados);
        $filas = [];
        $totales = ['faltan' => 0, 'vencidos' => 0, 'proximos' => 0, 'sin_fecha' => 0, 'total' => 0];

        foreach ($empleados as $empleado) {
            $problemas = array_values(array_filter(
                $docs[$empleado->id] ?? [],
                fn ($a) => in_array($a['estado'], EstadoDocumentacion::PROBLEMAS, true),
            ));

            if (! $problemas) {
                continue;
            }

            $resumen = EstadoDocumentacion::resumen($problemas);
            foreach ($totales as $clave => $valor) {
                $totales[$clave] += $resumen[$clave];
            }

            $filas[] = [
                'empleado' => [
                    'id' => $empleado->id,
                    'nombre_completo' => $empleado->nombre_completo,
                    'dni' => $empleado->dni,
                    'puesto' => $empleado->puesto,
                    'obras' => $empleado->obras->pluck('nombre')->all(),
                ],
                'problemas' => $problemas,
            ];
        }

        // Formación caducada o que caduca pronto (y no renovada).
        $cursos = CursoController::conEstado(
            Curso::whereIn('empleado_id', $empleados->pluck('id'))->orderBy('fecha')->get()
        );
        $formacion = $cursos
            ->filter(fn ($c) => in_array($c->estado, ['vencido', 'proximo'], true))
            ->sortBy('fecha_caducidad')
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'estado' => $c->estado,
                'caduca' => $c->fecha_caducidad->toDateString(),
                'dias' => $c->dias,
                'empleado' => [
                    'id' => $c->empleado_id,
                    'nombre_completo' => $empleados->firstWhere('id', $c->empleado_id)?->nombre_completo,
                ],
            ])
            ->values();

        return response()->json(['empleados' => $filas, 'totales' => $totales, 'formacion' => $formacion]);
    }

    /**
     * Buscador de obras para los selectores (máx. 20 resultados): primero
     * las que están en ejecución o planificación.
     */
    public function buscarObras(Request $request)
    {
        $query = Obra::query()->select(['id', 'nombre', 'estado']);

        if ($busqueda = trim((string) $request->input('search'))) {
            $query->where('nombre', 'like', '%'.addcslashes($busqueda, '%_\\').'%');
        }

        return response()->json([
            'obras' => $query
                ->orderByRaw("FIELD(estado, 'ejecucion', 'planificacion', 'en_pausa', 'finalizada')")
                ->orderBy('nombre')
                ->limit(20)
                ->get(),
        ]);
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    private function ficha(Empleado $empleado): array
    {
        $empleado->loadMissing([self::OBRAS, 'periodos']);

        $apartados = $this->documentacion->paraEmpleados(collect([$empleado]), true)[$empleado->id] ?? [];

        return [
            'empleado' => $empleado,
            'documentacion' => $apartados,
            'resumen_documentacion' => EstadoDocumentacion::resumen($apartados),
            'carpeta' => $empleado->folder_id
                ? ['id' => $empleado->folder_id, 'ruta' => Folder::find($empleado->folder_id)?->rutaCompleta()]
                : null,
            'opciones' => $this->opciones(),
        ];
    }

    private function opciones(): array
    {
        return [
            'tipos_contrato' => Empleado::TIPOS_CONTRATO,
            'jornadas' => Empleado::JORNADAS,
            'motivos_baja' => Empleado::MOTIVOS_BAJA,
            'vacaciones' => RrhhTipoAusencia::where('es_vacaciones', true)->first(['id', 'nombre', 'dias_anuales', 'computo']),
        ];
    }

    /** DNI e IBAN en mayúsculas y sin separadores; NSS solo dígitos. */
    private function normalizar(Request $request): void
    {
        $limpio = [];

        if ($request->filled('dni')) {
            $limpio['dni'] = strtoupper(preg_replace('/[\s.\-]/', '', (string) $request->input('dni')));
        }
        if ($request->filled('iban')) {
            $limpio['iban'] = strtoupper(preg_replace('/\s+/', '', (string) $request->input('iban')));
        }
        if ($request->filled('nss')) {
            $limpio['nss'] = preg_replace('/\D/', '', (string) $request->input('nss'));
        }

        $request->merge($limpio);
    }

    private function reglas(?Empleado $empleado = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'string', 'max:20', new DniNie, Rule::unique('empleados', 'dni')->ignore($empleado?->id)],
            'nss' => ['nullable', 'string', 'regex:/^\d{12}$/'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'poblacion' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'puesto' => ['nullable', 'string', 'max:100'],
            'categoria_convenio' => ['nullable', 'string', 'max:100'],
            'tipo_contrato' => ['nullable', Rule::in(array_keys(Empleado::TIPOS_CONTRATO))],
            'jornada' => ['nullable', Rule::in(array_keys(Empleado::JORNADAS))],
            'horas_semanales' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'salario_bruto_anual' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'dias_vacaciones_anuales' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'iban' => ['nullable', 'string', new Iban],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_relacion' => ['nullable', 'string', 'max:60'],
            'contacto_emergencia_telefono' => ['nullable', 'string', 'max:30'],
            'obra_ids' => ['nullable', 'array', 'max:50'],
            'obra_ids.*' => ['integer', 'distinct', Rule::exists('obras', 'id')],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'dni.unique' => 'Ya existe un empleado con ese DNI/NIE.',
            'nss.regex' => 'El nº de la Seguridad Social debe tener 12 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }
}
