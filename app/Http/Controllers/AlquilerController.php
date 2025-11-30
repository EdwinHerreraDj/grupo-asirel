<?php

namespace App\Http\Controllers;

use App\Exports\AlquileresObraExport;
use App\Models\Alquiler;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AlquilerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $obra = Obra::with('alquileres')->findOrFail($id);

        return view('obras.gastos.alquileres.index', compact('obra'));
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
            'numero_factura' => 'nullable|string',
            'fecha' => 'nullable|date',
            'archivo_factura' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
        ]);

        // Calcula el importe
        $importe = $request->precio_unitario * $request->cantidad;

        // Guarda el archivo si existe
        $archivoPath = null;
        if ($request->hasFile('archivo_factura')) {
            $archivoPath = $request->file('archivo_factura')->store('facturas_alquileres', 'public');
        }

        // Crea el registro
        Alquiler::create([
            'obra_id' => $request->obra_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_unitario' => $request->precio_unitario,
            'cantidad' => $request->cantidad,
            'importe' => $importe,
            'fecha' => $request->fecha,
            'numero_factura' => $request->numero_factura,
            'archivo_factura' => $archivoPath,
        ]);

        return redirect()->back()->with('success', 'Alquiler registrado correctamente');
    }

    public function destroy($id)
    {
        $alquiler = Alquiler::findOrFail($id);

        if ($alquiler->archivo_factura) {
            Storage::disk('public')->delete($alquiler->archivo_factura);
        }

        $alquiler->delete();

        return redirect()->back()->with('success', 'Gasto Alquiler eliminado correctamente');
    }

    public function verInforme($id)
    {
        $obra = Obra::findOrFail($id);
        $alquileres = Alquiler::where('obra_id', $id)->get();

        return view('obras.gastos.alquileres.pdf', compact('obra', 'alquileres'));
    }

    public function alquileresExcel(Request $request, $id)
    {
        $nombre = $request->input('nombre');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new AlquileresObraExport($id, $nombre, $fechaInicio, $fechaFin),
            'alquileres_obra_'.$id.'.xlsx'
        );
    }

    public function descargarPDF(Request $request, $id)
    {
        $obra = Obra::findOrFail($id);

        // Aplicar filtros si existen
        $query = Alquiler::where('obra_id', $id);

        if ($request->filled('nombre')) {
            $query->where('nombre', 'LIKE', "%{$request->nombre}%");
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $alquileres = $query->get();

        // Crear el PDF usando app() para evitar conflictos con alias
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('obras.gastos.alquileres.pdf', compact('obra', 'alquileres'))
            ->setPaper('a4', 'portrait');

        // Descargar el PDF
        return $pdf->download('alquileres_obra_'.$id.'.pdf');
    }
}
