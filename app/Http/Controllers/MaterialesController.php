<?php

namespace App\Http\Controllers;

use App\Exports\MaterialesObraExport;
use App\Models\Material;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MaterialesController extends Controller
{
    public function index($id)
    {
        $obra = Obra::with('materiales')->findOrFail($id);

        return view('obras.gastos.materiales.index', compact('obra'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'obra_id' => 'required|exists:obras,id',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_unitario' => 'required|numeric',
            'cantidad' => 'required|integer',
            'fecha' => 'required|date',
            'archivo_factura' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
            'numero_factura' => 'nullable|string',
        ]);

        // Calcula el importe
        $importe = $request->precio_unitario * $request->cantidad;

        // Guarda el archivo si existe
        $archivoPath = null;
        if ($request->hasFile('archivo_factura')) {
            $archivoPath = $request->file('archivo_factura')->store('facturas_materiales', 'public');
        }

        // Crea el registro
        Material::create([
            'obra_id' => $request->obra_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_unitario' => $request->precio_unitario,
            'cantidad' => $request->cantidad,
            'fecha' => $request->fecha,
            'importe' => $importe,
            'archivo_factura' => $archivoPath,
            'numero_factura' => $request->numero_factura,
        ]);

        return redirect()->back()->with('success', 'Material registrado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $material = Material::findOrFail($id);

        if ($material->archivo_factura) {
            Storage::disk('public')->delete($material->archivo_factura);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Gasto de material eliminado correctamente.');
    }

    public function verInforme($id)
    {
        $obra = Obra::findOrFail($id);
        $materiales = Material::where('obra_id', $id)->get();

        return view('obras.gastos.materiales.pdf', compact('obra', 'materiales'));
    }

    /* Informe de materiales */
    public function materialesExcel(Request $request, $id)
    {
        $nombre = $request->input('nombre');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new MaterialesObraExport($id, $nombre, $fechaInicio, $fechaFin),
            'materiales_obra_'.$id.'.xlsx'
        );
    }

    public function descargarPDF(Request $request, $id)
    {
        $obra = Obra::findOrFail($id);

        // Aplicar filtros si existen
        $query = Material::where('obra_id', $id);

        if ($request->filled('nombre')) {
            $query->where('nombre', $request->nombre);
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $materiales = $query->get();

        // Crear el PDF usando app() en lugar de Pdf::
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('obras.gastos.materiales.pdf', compact('obra', 'materiales'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('informe_obra_'.$id.'.pdf');
    }
}
