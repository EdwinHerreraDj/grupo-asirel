<?php

namespace App\Http\Controllers;

use App\Exports\InformeGeneralExport;
use App\Models\Obra;
use App\Models\ResumenFichajeMensual;
use App\Models\FacturaRecibida;
use App\Models\Certificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ObraController extends Controller
{
    public function create()
    {
        return view('obras.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:255',
                'estado' => 'required|string|in:planificacion,ejecucion,finalizada',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'latitud' => 'nullable|numeric|between:-90,90',
                'longitud' => 'nullable|numeric|between:-180,180',
                'radio' => 'nullable|numeric|min:0',
                'importe_presupuestado' => 'required|numeric|min:0',
                'descripcion' => 'nullable|string',
            ],
            [

                'longitud.between' => 'La longitud debe estar entre -180 y 180 grados.',
                'latitud.between' => 'La latitud debe estar entre -90 y 90 grados.',

            ]
        );

        try {
            DB::beginTransaction();

            // 1️⃣ Crear la obra
            $obra = Obra::create([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'radio' => $request->radio ?? 0,
                'importe_presupuestado' => $request->importe_presupuestado,
                'descripcion' => $request->descripcion,
            ]);

            // 2️⃣ Guardar gastos iniciales (si existen)
            if ($request->has('gastos_iniciales')) {
                foreach ($request->gastos_iniciales as $categoria_id => $importe) {
                    if ($importe !== null && $importe !== "") {
                        $obra->gastosIniciales()->attach($categoria_id, [
                            'importe' => $importe,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('unidad')
                ->with('success', 'Obra creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function edit($id)
    {
        $obra = Obra::findOrFail($id);

        return view('obras.edit', compact('obra'));
    }

    /**
     * Actualizar los datos de la obra.
     */
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:255',
                'estado' => 'required|string|in:planificacion,ejecucion,finalizada',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'importe_presupuestado' => 'required|numeric|min:0',
                'radio' => 'nullable|numeric|min:0',
                'latitud' => 'nullable|numeric|between:-90,90',
                'longitud' => 'nullable|numeric|between:-180,180',
                'descripcion' => 'nullable|string',
            ],
            [
                'longitud.between' => 'La longitud debe estar entre -180 y 180 grados.',
                'latitud.between' => 'La latitud debe estar entre -90 y 90 grados.',
            ]
        );

        try {
            DB::beginTransaction();

            // 1️⃣ Obtener obra
            $obra = Obra::findOrFail($id);

            // 2️⃣ Actualizar datos principales
            $obra->update([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'importe_presupuestado' => $request->importe_presupuestado,
                'radio' => $request->radio ?? 0,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'descripcion' => $request->descripcion,
            ]);

            // 3️⃣ Actualizar gastos iniciales (pivot)
            $obra->gastosIniciales()->detach(); // limpia todo

            if ($request->has('gastos_iniciales')) {
                foreach ($request->gastos_iniciales as $categoria_id => $importe) {
                    if ($importe !== null && $importe !== "") {
                        $obra->gastosIniciales()->attach($categoria_id, [
                            'importe' => $importe,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('unidad')
                ->with('success', 'Obra actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function destroy($id)
    {
        try {
            // Encuentra la obra por su ID
            $obra = Obra::findOrFail($id);

            $obra->delete();

            return redirect()->route('unidad')->with('success', 'Obra eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('obras.index')->with('success', 'No se pudo eliminar la obra: ' . $e->getMessage());
        }
    }

    public function informeGeneral($id)
    {
        $obra = Obra::findOrFail($id);

        /* 
    |--------------------------------------------------------------------------
    | 1) GASTOS (Facturas recibidas pagadas)
    |--------------------------------------------------------------------------
    */
        $facturas = FacturaRecibida::where('obra_id', $id)
            ->where('estado', 'pagada')
            ->get();

        $totalGastos = $facturas->sum('importe');

        /*
    |--------------------------------------------------------------------------
    | 2) INGRESOS (Certificaciones)
    |--------------------------------------------------------------------------
    */
        $certificaciones = Certificacion::where('obra_id', $id)
            ->where('tipo_documento', 'certificacion')
            ->get();

        $totalVentas = $certificaciones->sum('total');

        /*
    |--------------------------------------------------------------------------
    | 3) RESULTADO & BALANCE
    |--------------------------------------------------------------------------
    */
        $resultado = $totalVentas - $totalGastos;

        if ($totalGastos > 0) {
            $balance = round(($totalVentas / $totalGastos) * 100, 2);
        } else {
            $balance = $totalVentas > 0 ? 100 : 0;
        }

        $rentable = $resultado >= 0 ? 'Rentable' : 'No rentable';

        /*
    |--------------------------------------------------------------------------
    | 4) GENERACIÓN DEL PDF
    |--------------------------------------------------------------------------
    */
        $pdf = app('dompdf.wrapper');

        $pdf->loadView(
            'obras.pdf-general',
            compact(
                'obra',
                'facturas',
                'certificaciones',
                'totalGastos',
                'totalVentas',
                'resultado',
                'balance',
                'rentable'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->download('informe_general_obra_' . $id . '.pdf');
    }



    public function informeGeneralExcel($id)
    {
        $obra = \App\Models\Obra::findOrFail($id);
        $nombreArchivo = 'informe_general_obra_' . $obra->id . '.xlsx';

        return Excel::download(new InformeGeneralExport($obra->id), $nombreArchivo);
    }
}
