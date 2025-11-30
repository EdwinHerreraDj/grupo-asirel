<?php

namespace App\Http\Controllers;

use App\Exports\VentasObraExport;
use App\Models\Obra;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $obra = Obra::with('ventas')->findOrFail($id);

        return view('obras.ventas.index', compact('obra'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
            'importe' => 'required|numeric',
            'fecha' => 'required|date',
            'archivo_certificado' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
        ]);

        // Guarda el archivo si existe
        $archivoPath = null;
        if ($request->hasFile('archivo_certificado')) {
            $archivoPath = $request->file('archivo_certificado')->store('certificados_ventas', 'public');
        }

        // Crea el registro
        Venta::create([
            'obra_id' => $request->obra_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'importe' => $request->importe,
            'fecha' => $request->fecha,
            'archivo_certificado' => $archivoPath,
        ]);

        return redirect()->back()->with('success', 'Alquiler registrado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        if ($venta->archivo_certificado) {
            Storage::disk('public')->delete($venta->archivo_certificado);
        }
        $venta->delete();

        return redirect()->back()->with('success', 'Venta eliminada correctamente');
    }

    public function verInforme($id)
    {
        $obra = Obra::findOrFail($id);
        $ventas = Venta::where('obra_id', $id)->get();

        return view('obras.ventas.pdf', compact('ventas', 'obra'));
    }

    public function ventasExcel(Request $request, $id)
    {
        $nombre = $request->input('nombre');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new VentasObraExport($id, $nombre, $fechaInicio, $fechaFin),
            'ventas_obra_'.$id.'.xlsx'
        );
    }

    public function descargarPDF(Request $request, $id)
    {
        // Buscar la obra
        $obra = Obra::findOrFail($id);

        // Aplicar filtros si existen
        $query = Venta::where('obra_id', $id);

        // Filtro por nombre
        if ($request->filled('nombre')) {
            $query->where('nombre', 'LIKE', "%{$request->nombre}%");
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        // Obtener los registros filtrados
        $ventas = $query->get();

        // Crear el PDF usando app()
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('obras.ventas.pdf', compact('obra', 'ventas'))
            ->setPaper('a4', 'portrait');

        // Descargar el PDF
        return $pdf->download('ventas_obra_'.$id.'.pdf');
    }
}
