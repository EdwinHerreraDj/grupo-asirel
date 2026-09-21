<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\RrhhTipoAusencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Tipos de ausencia configurables. No se borran: se desactivan (las
 * ausencias registradas los siguen usando).
 */
class TipoAusenciaController extends Controller
{
    public function index()
    {
        return response()->json([
            'tipos' => RrhhTipoAusencia::orderBy('orden')->orderBy('nombre')->get(),
            'colores' => RrhhTipoAusencia::COLORES,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $tipo = RrhhTipoAusencia::create($datos + [
            'orden' => (int) RrhhTipoAusencia::max('orden') + 1,
        ])->refresh();

        return response()->json(['message' => 'Tipo de ausencia creado', 'tipo' => $tipo], 201);
    }

    public function update(Request $request, RrhhTipoAusencia $tipo)
    {
        $tipo->update($this->validar($request, $tipo));

        return response()->json(['message' => 'Tipo de ausencia actualizado', 'tipo' => $tipo->fresh()]);
    }

    private function validar(Request $request, ?RrhhTipoAusencia $tipo = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('rrhh_tipos_ausencia', 'nombre')->ignore($tipo?->id)],
            'color' => ['required', Rule::in(RrhhTipoAusencia::COLORES)],
            'es_vacaciones' => ['boolean'],
            'es_baja_medica' => ['boolean'],
            'retribuida' => ['boolean'],
            'requiere_justificante' => ['boolean'],
            'dias_anuales' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'computo' => ['required', Rule::in([RrhhTipoAusencia::COMPUTO_NATURALES, RrhhTipoAusencia::COMPUTO_LABORABLES])],
            'activo' => ['boolean'],
        ], [
            'nombre.unique' => 'Ya existe un tipo de ausencia con ese nombre.',
        ]);

        if (! empty($datos['es_vacaciones'])) {
            if (! empty($datos['es_baja_medica'])) {
                throw ValidationException::withMessages(['es_baja_medica' => 'Un tipo no puede ser vacaciones y baja médica a la vez.']);
            }
            if (($datos['dias_anuales'] ?? null) === null) {
                throw ValidationException::withMessages(['dias_anuales' => 'Indica los días de vacaciones al año.']);
            }
            $otro = RrhhTipoAusencia::where('es_vacaciones', true)
                ->when($tipo, fn ($q) => $q->where('id', '!=', $tipo->id))
                ->first();
            if ($otro) {
                throw ValidationException::withMessages(['es_vacaciones' => "Ya está marcado como vacaciones el tipo «{$otro->nombre}»."]);
            }
        }

        return $datos;
    }
}
