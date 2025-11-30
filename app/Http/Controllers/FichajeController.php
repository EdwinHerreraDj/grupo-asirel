<?php

namespace App\Http\Controllers;

use App\Exports\FichajesObraMensualExport;
use App\Models\Obra;
use App\Models\ResumenFichajeMensual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FichajeController extends Controller
{
    public function index($obraId, Request $request)
    {
        $this->importarResumen($obraId);
        $obra = Obra::findOrFail($obraId);
        $resumen = ResumenFichajeMensual::where('obra_id', $obraId)
            ->orderBy('mes', 'desc')
            ->get();

        return view('obras.gastos.fichaje.index', compact('resumen', 'obra'));
    }

    protected function importarResumen($obraId)
    {
        // Obtener todos los fichajes desde la conexión 'presencia'
        $fichajes = DB::connection('presencia')->table('fichajes')
            ->where('empresa_id', $obraId)
            ->orderBy('fecha_hora')
            ->get();

        if ($fichajes->isEmpty()) {
            return;
        }

        // Obtener empleados implicados
        $empleados = DB::connection('presencia')->table('empleados')
            ->whereIn('id', $fichajes->pluck('empleado_id')->unique())
            ->get()
            ->keyBy('id');

        // Agrupar por empleado y mes (formato 'Y-m')
        $agrupado = $fichajes->groupBy([
            'empleado_id',
            fn ($item) => Carbon::parse($item->fecha_hora)->format('Y-m'),
        ]);

        foreach ($agrupado as $empleadoId => $porMes) {
            $empleado = $empleados->get($empleadoId);
            if (! $empleado) {
                continue;
            }

            foreach ($porMes as $mes => $lista) {
                $lista = collect($lista)->sortBy('fecha_hora')->values();
                $minutos = 0;
                $entrada = null;

                foreach ($lista as $fichaje) {
                    if ($fichaje->tipo === 'entrada') {
                        $entrada = $fichaje;
                    } elseif ($fichaje->tipo === 'salida' && $entrada) {
                        $minutos += Carbon::parse($entrada->fecha_hora)->diffInMinutes(Carbon::parse($fichaje->fecha_hora));
                        $entrada = null;
                    }
                }

                $horas = round($minutos / 60, 2);
                $esPorMetros = $empleado->trabaja_por_metros;
                $tarifa = $esPorMetros ? null : ($empleado->tarifa_hora ?? 0);
                $total = $esPorMetros ? null : round($horas * $tarifa, 2);

                $registro = ResumenFichajeMensual::where([
                    ['obra_id', '=', $obraId],
                    ['empleado_id', '=', $empleadoId],
                    ['mes', '=', $mes],
                ])->first();

                ResumenFichajeMensual::updateOrCreate(
                    [
                        'obra_id' => $obraId,
                        'empleado_id' => $empleadoId,
                        'mes' => $mes,
                    ],
                    [
                        'horas_trabajadas' => $horas,
                        'tarifa_hora' => $tarifa,
                        'total_ganado' => optional($registro)->total_ganado ?? $total,
                        'metros_realizados' => optional($registro)->metros_realizados,
                    ]
                );
            }
        }
    }

    public function update(Request $request, $id)
    {
        $resumen = ResumenFichajeMensual::findOrFail($id);

        $data = [];

        if ($request->has('metros_realizados')) {
            $data['metros_realizados'] = $request->input('metros_realizados');
        }

        if ($request->has('total_ganado')) {
            $data['total_ganado'] = $request->input('total_ganado');
        }

        if (! empty($data)) {
            $resumen->update($data);
        }

        return back()->with('success', 'Resumen actualizado correctamente.');
    }

    public function fichajesExcel($obraId, Request $request)
    {
        $mes = $request->input('mes');
        $anio = $request->input('anio');

        return Excel::download(
            new FichajesObraMensualExport($obraId, $mes, $anio),
            "resumen_fichajes_{$mes}_{$anio}.xlsx"
        );
    }
}
