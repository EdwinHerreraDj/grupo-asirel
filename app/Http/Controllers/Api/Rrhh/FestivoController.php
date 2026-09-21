<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Festivo;
use App\Services\Rrhh\CalendarioLaboral;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Calendario de festivos de la empresa (cuentan para los días laborables). */
class FestivoController extends Controller
{
    public function index(Request $request)
    {
        $anio = $this->anio($request);

        return response()->json([
            'anio' => $anio,
            'festivos' => Festivo::whereYear('fecha', $anio)->orderBy('fecha')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date', Rule::unique('rrhh_festivos', 'fecha')],
            'nombre' => ['required', 'string', 'max:150'],
        ], [
            'fecha.unique' => 'Ese día ya es festivo.',
        ]);

        $festivo = Festivo::create($datos);

        return response()->json(['message' => 'Festivo añadido', 'festivo' => $festivo], 201);
    }

    public function destroy(Festivo $festivo)
    {
        $festivo->delete();

        return response()->json(['message' => 'Festivo eliminado']);
    }

    /** Añade los festivos nacionales del año que falten. */
    public function nacionales(Request $request)
    {
        $anio = $this->anio($request);
        $creados = 0;

        foreach (CalendarioLaboral::festivosNacionales($anio) as $fecha => $nombre) {
            $festivo = Festivo::firstOrCreate(['fecha' => $fecha], ['nombre' => $nombre]);
            $creados += $festivo->wasRecentlyCreated ? 1 : 0;
        }

        return response()->json([
            'message' => $creados
                ? "Añadidos {$creados} festivos nacionales de {$anio}"
                : "Los festivos nacionales de {$anio} ya estaban añadidos",
            'creados' => $creados,
        ]);
    }

    private function anio(Request $request): int
    {
        $anio = (int) $request->input('anio', date('Y'));

        return $anio >= 2000 && $anio <= 2100 ? $anio : (int) date('Y');
    }
}
