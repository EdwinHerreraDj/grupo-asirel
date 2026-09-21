<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Empleado;
use App\Services\Rrhh\AdjuntosRrhh;
use App\Services\Rrhh\CarpetasEmpleados;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Cursos y formación. Estado según la caducidad; un curso con el mismo nombre
 * hecho más tarde lo "renueva" y deja de avisar.
 */
class CursoController extends Controller
{
    public function __construct(
        private readonly AdjuntosRrhh $adjuntos,
    ) {}

    public function index(Empleado $empleado)
    {
        $cursos = Curso::where('empleado_id', $empleado->id)
            ->with('archivo:id,nombre')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'cursos' => self::conEstado($cursos)->values(),
            'horas' => round($cursos->sum('horas'), 1),
            'categorias' => Curso::CATEGORIAS,
            'dias_aviso' => Curso::DIAS_AVISO,
        ]);
    }

    public function store(Request $request, Empleado $empleado)
    {
        $datos = $this->validar($request);

        $curso = DB::transaction(function () use ($request, $empleado, $datos) {
            $curso = Curso::create($datos + [
                'empleado_id' => $empleado->id,
                'usuario_id' => $request->user()->id,
            ]);
            $this->guardarArchivo($request, $empleado, $curso);

            return $curso;
        });

        return response()->json(['message' => 'Curso registrado', 'curso' => $curso->fresh('archivo:id,nombre')], 201);
    }

    public function update(Request $request, Curso $curso)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($request, $curso, $datos) {
            $curso->update($datos);
            $this->guardarArchivo($request, $curso->empleado, $curso);
        });

        return response()->json(['message' => 'Curso actualizado', 'curso' => $curso->fresh('archivo:id,nombre')]);
    }

    public function destroy(Curso $curso)
    {
        $curso->delete();

        return response()->json(['message' => 'Curso eliminado']);
    }

    /**
     * Añade `estado` (sin_caducidad | vigente | proximo | vencido | renovado)
     * y `dias` hasta la caducidad a cada curso.
     */
    public static function conEstado(Collection $cursos): Collection
    {
        $hoy = today();

        return $cursos->each(function (Curso $c) use ($cursos, $hoy) {
            $renovado = $cursos->contains(fn (Curso $o) => $o->id !== $c->id
                && $o->empleado_id === $c->empleado_id
                && mb_strtolower(trim($o->nombre)) === mb_strtolower(trim($c->nombre))
                && ($o->fecha->gt($c->fecha) || ($o->fecha->eq($c->fecha) && $o->id > $c->id)));

            $dias = $c->fecha_caducidad ? (int) $hoy->diffInDays($c->fecha_caducidad, false) : null;

            $estado = match (true) {
                $renovado => 'renovado',
                $dias === null => 'sin_caducidad',
                $dias < 0 => 'vencido',
                $dias <= Curso::DIAS_AVISO => 'proximo',
                default => 'vigente',
            };

            $c->setAttribute('estado', $estado);
            $c->setAttribute('dias', $dias);
        });
    }

    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'categoria' => ['required', Rule::in(array_keys(Curso::CATEGORIAS))],
            'entidad' => ['nullable', 'string', 'max:150'],
            'horas' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'fecha' => ['required', 'date'],
            'fecha_caducidad' => ['nullable', 'date', 'after_or_equal:fecha'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'archivo' => ['nullable', 'file', 'max:51200'],
        ], [
            'fecha_caducidad.after_or_equal' => 'La caducidad no puede ser anterior a la fecha del curso.',
        ]);
        unset($datos['archivo']);

        return $datos;
    }

    private function guardarArchivo(Request $request, Empleado $empleado, Curso $curso): void
    {
        if (! $request->hasFile('archivo')) {
            return;
        }

        $prefijo = $curso->fecha->toDateString().' '.$curso->nombre;
        $file = $this->adjuntos->guardar($empleado, $request->file('archivo'), CarpetasEmpleados::SISTEMA_FORMACION, $prefijo);
        $curso->update(['file_id' => $file->id]);
    }
}
