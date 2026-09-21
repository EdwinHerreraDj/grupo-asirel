<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Ausencia;
use App\Models\Empleado;
use App\Models\File;
use App\Models\RrhhTipoAusencia;
use App\Services\Drive\DriveStorage;
use App\Services\Rrhh\CalendarioLaboral;
use App\Services\Rrhh\CarpetasEmpleados;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Ausencias de un empleado (vacaciones, bajas médicas, permisos…) con su
 * saldo anual. El justificante se guarda en el Drive, en la carpeta
 * "Ausencias y bajas" del empleado.
 */
class AusenciaController extends Controller
{
    public function __construct(
        private readonly CalendarioLaboral $calendario,
        private readonly CarpetasEmpleados $carpetas,
        private readonly DriveStorage $storage,
    ) {}

    public function index(Request $request, Empleado $empleado)
    {
        $anio = $this->anio($request);
        $ini = "{$anio}-01-01";
        $fin = "{$anio}-12-31";

        $ausencias = Ausencia::where('empleado_id', $empleado->id)
            ->solapadas($ini, $fin)
            ->with(['tipo', 'archivo:id,nombre'])
            ->orderByDesc('fecha_inicio')
            ->get();

        $resumen = [];
        foreach ($ausencias as $a) {
            $dias = $this->calendario->diasDe(
                $a,
                CarbonImmutable::parse($ini),
                CarbonImmutable::parse($fin),
            );
            $resumen[$a->rrhh_tipo_ausencia_id] ??= [
                'tipo_id' => $a->tipo->id,
                'tipo' => $a->tipo->nombre,
                'color' => $a->tipo->color,
                'naturales' => 0,
                'laborables' => 0,
            ];
            $resumen[$a->rrhh_tipo_ausencia_id]['naturales'] += $dias['naturales'];
            $resumen[$a->rrhh_tipo_ausencia_id]['laborables'] += $dias['laborables'];
        }

        $primerAlta = $empleado->periodos()->min('fecha_alta');
        $desde = $primerAlta ? (int) substr($primerAlta, 0, 4) : (int) date('Y');

        return response()->json([
            'anio' => $anio,
            'anios' => range((int) date('Y') + 1, min($desde, $anio)),
            'ausencias' => $ausencias->map(fn (Ausencia $a) => $this->formatear($a))->values(),
            'saldos' => $this->calendario->saldos($empleado, $anio),
            'resumen' => array_values($resumen),
            'tipos' => RrhhTipoAusencia::orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, Empleado $empleado)
    {
        [$datos, $tipo] = $this->validar($request, $empleado);

        $ausencia = DB::transaction(function () use ($request, $empleado, $datos) {
            $ausencia = Ausencia::create($datos + [
                'empleado_id' => $empleado->id,
                'usuario_id' => $request->user()->id,
            ]);

            if ($request->hasFile('justificante')) {
                $this->guardarJustificante($ausencia, $request->file('justificante'));
            }

            return $ausencia;
        });

        return response()->json([
            'message' => 'Ausencia registrada',
            'ausencia' => $this->formatear($ausencia->fresh(['tipo', 'archivo'])),
            'aviso' => $this->avisoSaldo($empleado, $tipo, $ausencia),
        ], 201);
    }

    public function update(Request $request, Ausencia $ausencia)
    {
        $empleado = $ausencia->empleado;
        [$datos, $tipo] = $this->validar($request, $empleado, $ausencia);

        DB::transaction(function () use ($request, $ausencia, $datos) {
            $ausencia->update($datos);

            if ($request->hasFile('justificante')) {
                $this->guardarJustificante($ausencia, $request->file('justificante'));
            }
        });

        return response()->json([
            'message' => 'Ausencia actualizada',
            'ausencia' => $this->formatear($ausencia->fresh(['tipo', 'archivo'])),
            'aviso' => $this->avisoSaldo($empleado, $tipo, $ausencia),
        ]);
    }

    /** El justificante se queda en el Drive (no se borra nada automáticamente). */
    public function destroy(Ausencia $ausencia)
    {
        $ausencia->delete();

        return response()->json(['message' => 'Ausencia eliminada']);
    }

    // -------------------------------------------------------------
    // Internos
    // -------------------------------------------------------------

    /** @return array{0: array, 1: RrhhTipoAusencia} */
    private function validar(Request $request, Empleado $empleado, ?Ausencia $actual = null): array
    {
        $datos = $request->validate([
            'rrhh_tipo_ausencia_id' => ['required', 'integer'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'justificante' => ['nullable', 'file', 'max:51200'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'justificante.max' => 'El justificante no puede superar 50 MB.',
        ]);
        unset($datos['justificante']);

        // Un tipo desactivado solo vale si la ausencia ya lo tenía.
        $tipo = RrhhTipoAusencia::find($datos['rrhh_tipo_ausencia_id']);
        if (! $tipo || (! $tipo->activo && $actual?->rrhh_tipo_ausencia_id !== $tipo->id)) {
            throw ValidationException::withMessages(['rrhh_tipo_ausencia_id' => 'Elige un tipo de ausencia válido.']);
        }

        $inicio = CarbonImmutable::parse($datos['fecha_inicio'])->toDateString();
        $fin = ! empty($datos['fecha_fin']) ? CarbonImmutable::parse($datos['fecha_fin'])->toDateString() : null;
        $datos['fecha_inicio'] = $inicio;
        $datos['fecha_fin'] = $fin;

        if (! $fin && ! $tipo->es_baja_medica) {
            throw ValidationException::withMessages([
                'fecha_fin' => 'Indica la fecha de fin. Solo las bajas médicas pueden quedar abiertas.',
            ]);
        }

        // Dentro de un periodo de alta.
        $dentroDeAlta = $empleado->periodos()
            ->where('fecha_alta', '<=', $inicio)
            ->where(fn ($q) => $fin
                ? $q->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $fin)
                : $q->whereNull('fecha_baja'))
            ->exists();

        if (! $dentroDeAlta) {
            throw ValidationException::withMessages([
                'fecha_inicio' => 'Las fechas deben estar dentro de un periodo de alta del empleado.',
            ]);
        }

        // Sin solaparse con otra ausencia.
        $solapada = Ausencia::where('empleado_id', $empleado->id)
            ->when($actual, fn ($q) => $q->where('id', '!=', $actual->id))
            ->solapadas($inicio, $fin)
            ->with('tipo:id,nombre')
            ->first();

        if ($solapada) {
            $hasta = $solapada->fecha_fin ? 'al '.$solapada->fecha_fin->format('d/m/Y') : '(sin fecha de fin)';
            throw ValidationException::withMessages([
                'fecha_inicio' => "Se solapa con otra ausencia: {$solapada->tipo->nombre} del {$solapada->fecha_inicio->format('d/m/Y')} {$hasta}.",
            ]);
        }

        return [$datos, $tipo];
    }

    private function guardarJustificante(Ausencia $ausencia, UploadedFile $archivo): void
    {
        $ausencia->loadMissing(['empleado', 'tipo']);
        $carpeta = $this->carpetas->carpetaAusencias($ausencia->empleado);
        $guardado = $this->storage->guardarSubida($archivo);

        try {
            $file = File::create([
                'folder_id' => $carpeta->id,
                'usuario_id' => auth()->id(),
                'nombre' => Str::limit(
                    $ausencia->fecha_inicio->toDateString().' '.$ausencia->tipo->nombre.' - '.$archivo->getClientOriginalName(),
                    250,
                    '',
                ),
                'ruta' => $guardado['ruta'],
                'disco' => $guardado['disco'],
                'tipo' => $archivo->getMimeType(),
                'tamaño' => $archivo->getSize(),
                'tiene_caducidad' => false,
            ]);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk($guardado['disco'])->delete($guardado['ruta']);
            throw $e;
        }

        $ausencia->update(['file_id' => $file->id]);
    }

    private function formatear(Ausencia $a): array
    {
        return [
            'id' => $a->id,
            'empleado_id' => $a->empleado_id,
            'rrhh_tipo_ausencia_id' => $a->rrhh_tipo_ausencia_id,
            'tipo' => $a->tipo?->only(['id', 'nombre', 'color', 'es_baja_medica', 'es_vacaciones', 'retribuida']),
            'fecha_inicio' => $a->fecha_inicio->toDateString(),
            'fecha_fin' => $a->fecha_fin?->toDateString(),
            'abierta' => $a->fecha_fin === null,
            'dias' => $this->calendario->diasDe($a),
            'observaciones' => $a->observaciones,
            'archivo' => $a->archivo ? ['id' => $a->archivo->id, 'nombre' => $a->archivo->nombre] : null,
        ];
    }

    /** Aviso (no bloquea) si con esta ausencia se pasa del saldo del año. */
    private function avisoSaldo(Empleado $empleado, RrhhTipoAusencia $tipo, Ausencia $ausencia): ?string
    {
        if ($tipo->dias_anuales === null || ! $tipo->activo) {
            return null;
        }

        $anio = $ausencia->fecha_inicio->year;
        $saldo = collect($this->calendario->saldos($empleado->fresh(), $anio, collect([$tipo])))->first();

        if ($saldo && $saldo['pendientes'] < 0) {
            $exceso = abs($saldo['pendientes']);

            return "Atención: supera en {$exceso} días los de {$tipo->nombre} de {$anio}.";
        }

        return null;
    }

    private function anio(Request $request): int
    {
        $anio = (int) $request->input('anio', date('Y'));

        return $anio >= 2000 && $anio <= 2100 ? $anio : (int) date('Y');
    }
}
