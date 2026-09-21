<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\RrhhTipoAusencia;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Tipos de turno (horarios). No se borran: se desactivan. */
class TurnoController extends Controller
{
    public function index()
    {
        return response()->json([
            'turnos' => Turno::orderBy('orden')->orderBy('nombre')->get(),
            'colores' => RrhhTipoAusencia::COLORES,
        ]);
    }

    public function store(Request $request)
    {
        $turno = Turno::create($this->validar($request) + ['orden' => (int) Turno::max('orden') + 1])->refresh();

        return response()->json(['message' => 'Turno creado', 'turno' => $turno], 201);
    }

    public function update(Request $request, Turno $turno)
    {
        $turno->update($this->validar($request, $turno));

        return response()->json(['message' => 'Turno actualizado', 'turno' => $turno->fresh()]);
    }

    private function validar(Request $request, ?Turno $turno = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('rrhh_turnos', 'nombre')->ignore($turno?->id)],
            'color' => ['required', Rule::in(RrhhTipoAusencia::COLORES)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'hora_inicio_2' => ['nullable', 'required_with:hora_fin_2', 'date_format:H:i'],
            'hora_fin_2' => ['nullable', 'required_with:hora_inicio_2', 'date_format:H:i'],
            'descanso_minutos' => ['nullable', 'integer', 'min:0', 'max:600'],
            'activo' => ['boolean'],
        ], [
            'nombre.unique' => 'Ya existe un turno con ese nombre.',
            'hora_inicio_2.required_with' => 'Indica el inicio del segundo tramo.',
            'hora_fin_2.required_with' => 'Indica el fin del segundo tramo.',
        ]);

        $datos['descanso_minutos'] = $datos['descanso_minutos'] ?? 0;

        return $datos;
    }
}
