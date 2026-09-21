<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Exports\Rrhh\TablaExport;
use App\Http\Controllers\Controller;
use App\Services\Rrhh\InformesRrhh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/** Informes de Recursos humanos: cifras del año y exportaciones a Excel. */
class InformeController extends Controller
{
    public function __construct(
        private readonly InformesRrhh $informes,
    ) {}

    public function resumen(Request $request)
    {
        $anio = (int) $request->input('anio', date('Y'));

        return response()->json($this->informes->resumen($anio >= 2000 && $anio <= 2100 ? $anio : (int) date('Y')));
    }

    public function exportar(Request $request, string $tipo)
    {
        abort_unless(array_key_exists($tipo, InformesRrhh::TIPOS), 404);

        $conRango = in_array($tipo, ['altas-bajas', 'ausencias', 'horas-obra'], true);
        $conAnio = in_array($tipo, ['vacaciones', 'nominas'], true);

        $p = $request->validate([
            'desde' => [$conRango ? 'required' : 'nullable', 'date'],
            'hasta' => [$conRango ? 'required' : 'nullable', 'date', 'after_or_equal:desde'],
            'anio' => [$conAnio ? 'required' : 'nullable', 'integer', 'between:2000,2100'],
            'estado' => ['nullable', Rule::in(['activo', 'baja', 'todos'])],
        ]);

        [$titulo, $cabecera, $filas] = $this->informes->tabla($tipo, $p);

        $sufijo = $conRango ? "_{$p['desde']}_{$p['hasta']}" : ($conAnio ? "_{$p['anio']}" : '_'.date('Y-m-d'));

        return Excel::download(new TablaExport($titulo, $cabecera, $filas), "rrhh_{$tipo}{$sufijo}.xlsx");
    }
}
