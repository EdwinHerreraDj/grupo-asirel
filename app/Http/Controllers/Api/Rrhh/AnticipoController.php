<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Anticipo;
use App\Models\Empleado;
use App\Services\Rrhh\AdjuntosRrhh;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Anticipos y vales entregados a cuenta. Una vez descontados en una nómina
 * no se pueden cambiar ni borrar (hay que quitarlos antes de esa nómina).
 */
class AnticipoController extends Controller
{
    private const CON = ['nomina:id,anio,mes,tipo', 'archivo:id,nombre'];

    public function __construct(
        private readonly AdjuntosRrhh $adjuntos,
    ) {}

    public function index(Empleado $empleado)
    {
        $anticipos = Anticipo::where('empleado_id', $empleado->id)
            ->with(self::CON)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $pendientes = $anticipos->whereNull('nomina_id');

        return response()->json([
            'anticipos' => $anticipos->values(),
            'pendiente' => round($pendientes->sum(fn ($a) => (float) $a->importe), 2),
            'n_pendientes' => $pendientes->count(),
            'tipos' => Anticipo::TIPOS,
            'formas_pago' => Anticipo::FORMAS_PAGO,
        ]);
    }

    public function store(Request $request, Empleado $empleado)
    {
        $datos = $this->validar($request, $empleado);

        $anticipo = DB::transaction(function () use ($request, $empleado, $datos) {
            $anticipo = Anticipo::create($datos + [
                'empleado_id' => $empleado->id,
                'usuario_id' => $request->user()->id,
            ]);
            $this->guardarArchivo($request, $empleado, $anticipo);

            return $anticipo;
        });

        return response()->json(['message' => 'Anticipo registrado', 'anticipo' => $anticipo->fresh(self::CON)], 201);
    }

    public function update(Request $request, Anticipo $anticipo)
    {
        $this->asegurarPendiente($anticipo);
        $datos = $this->validar($request, $anticipo->empleado);

        DB::transaction(function () use ($request, $anticipo, $datos) {
            $anticipo->update($datos);
            $this->guardarArchivo($request, $anticipo->empleado, $anticipo);
        });

        return response()->json(['message' => 'Anticipo actualizado', 'anticipo' => $anticipo->fresh(self::CON)]);
    }

    public function destroy(Anticipo $anticipo)
    {
        $this->asegurarPendiente($anticipo);
        $anticipo->delete();

        return response()->json(['message' => 'Anticipo eliminado']);
    }

    private function validar(Request $request, Empleado $empleado): array
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(Anticipo::TIPOS))],
            'fecha' => ['required', 'date'],
            'importe' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'concepto' => ['nullable', 'string', 'max:255'],
            'forma_pago' => ['nullable', Rule::in(array_keys(Anticipo::FORMAS_PAGO))],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'archivo' => ['nullable', 'file', 'max:51200'],
        ], [
            'importe.min' => 'El importe debe ser mayor que 0.',
        ]);
        unset($datos['archivo']);

        if (! $empleado->estuvoDeAltaEntre($datos['fecha'], $datos['fecha'])) {
            throw ValidationException::withMessages(['fecha' => 'El empleado no estaba de alta en esa fecha.']);
        }

        return $datos;
    }

    private function asegurarPendiente(Anticipo $anticipo): void
    {
        if ($anticipo->nomina_id) {
            $n = $anticipo->nomina;
            throw ValidationException::withMessages([
                'anticipo' => "Ya se descontó en la nómina de {$n->mes}/{$n->anio}. Quítalo de esa nómina para cambiarlo o borrarlo.",
            ]);
        }
    }

    private function guardarArchivo(Request $request, Empleado $empleado, Anticipo $anticipo): void
    {
        if (! $request->hasFile('archivo')) {
            return;
        }

        $prefijo = $anticipo->fecha->toDateString().' '.Anticipo::TIPOS[$anticipo->tipo];
        $file = $this->adjuntos->guardar($empleado, $request->file('archivo'), CarpetasEmpleados::SISTEMA_ANTICIPOS, $prefijo);
        $anticipo->update(['file_id' => $file->id]);
    }
}
