<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Sancion;
use App\Services\Rrhh\AdjuntosRrhh;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Sanciones disciplinarias (con la carta de sanción en el Drive). */
class SancionController extends Controller
{
    public function __construct(
        private readonly AdjuntosRrhh $adjuntos,
    ) {}

    public function index(Empleado $empleado)
    {
        return response()->json([
            'sanciones' => Sancion::where('empleado_id', $empleado->id)
                ->with('archivo:id,nombre')
                ->orderByDesc('fecha_hechos')
                ->orderByDesc('id')
                ->get(),
            'gravedades' => Sancion::GRAVEDADES,
            'tipos' => Sancion::TIPOS,
            'estados' => Sancion::ESTADOS,
            'prescripcion' => Sancion::PRESCRIPCION_DIAS,
        ]);
    }

    public function store(Request $request, Empleado $empleado)
    {
        $datos = $this->validar($request, $empleado);

        $sancion = DB::transaction(function () use ($request, $empleado, $datos) {
            $sancion = Sancion::create($datos + [
                'empleado_id' => $empleado->id,
                'usuario_id' => $request->user()->id,
            ]);
            $this->guardarArchivo($request, $empleado, $sancion);

            return $sancion;
        });

        return response()->json(['message' => 'Sanción registrada', 'sancion' => $sancion->fresh('archivo:id,nombre')], 201);
    }

    public function update(Request $request, Sancion $sancion)
    {
        $datos = $this->validar($request, $sancion->empleado);

        DB::transaction(function () use ($request, $sancion, $datos) {
            $sancion->update($datos);
            $this->guardarArchivo($request, $sancion->empleado, $sancion);
        });

        return response()->json(['message' => 'Sanción actualizada', 'sancion' => $sancion->fresh('archivo:id,nombre')]);
    }

    public function destroy(Sancion $sancion)
    {
        $sancion->delete();

        return response()->json(['message' => 'Sanción eliminada']);
    }

    private function validar(Request $request, Empleado $empleado): array
    {
        $datos = $request->validate([
            'fecha_hechos' => ['required', 'date'],
            'fecha_comunicacion' => ['nullable', 'date', 'after_or_equal:fecha_hechos'],
            'gravedad' => ['required', Rule::in(array_keys(Sancion::GRAVEDADES))],
            'tipo' => ['required', Rule::in(array_keys(Sancion::TIPOS))],
            'dias_suspension' => ['nullable', 'required_if:tipo,suspension', 'integer', 'min:1', 'max:365'],
            'fecha_inicio_suspension' => ['nullable', 'date', 'after_or_equal:fecha_hechos'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'estado' => ['required', Rule::in(array_keys(Sancion::ESTADOS))],
            'archivo' => ['nullable', 'file', 'max:51200'],
        ], [
            'dias_suspension.required_if' => 'Indica los días de suspensión.',
            'fecha_comunicacion.after_or_equal' => 'La comunicación no puede ser anterior a los hechos.',
            'fecha_inicio_suspension.after_or_equal' => 'La suspensión no puede empezar antes de los hechos.',
        ]);
        unset($datos['archivo']);

        if ($datos['tipo'] !== 'suspension') {
            $datos['dias_suspension'] = null;
            $datos['fecha_inicio_suspension'] = null;
        }

        if (! $empleado->estuvoDeAltaEntre($datos['fecha_hechos'], $datos['fecha_hechos'])) {
            throw ValidationException::withMessages(['fecha_hechos' => 'El empleado no estaba de alta en la fecha de los hechos.']);
        }

        return $datos;
    }

    private function guardarArchivo(Request $request, Empleado $empleado, Sancion $sancion): void
    {
        if (! $request->hasFile('archivo')) {
            return;
        }

        $prefijo = $sancion->fecha_hechos->toDateString().' Sanción '.mb_strtolower(Sancion::GRAVEDADES[$sancion->gravedad]);
        $file = $this->adjuntos->guardar($empleado, $request->file('archivo'), CarpetasEmpleados::SISTEMA_SANCIONES, $prefijo);
        $sancion->update(['file_id' => $file->id]);
    }
}
