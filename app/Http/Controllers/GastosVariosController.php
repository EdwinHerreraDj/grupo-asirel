<?php

namespace App\Http\Controllers;

use App\Exports\GastosVariosObraExport;
use App\Models\GastosVarios;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GastosVariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $obra = Obra::with('gastosVarios')->findOrFail($id);

        return view('obras.gastos.gastos-varios.index', compact('obra'));
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
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'importe' => 'required|numeric',
            'fecha' => 'nullable|date',
            'numero_factura' => 'nullable|string',
            'archivo_factura' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
        ]);

        // Guarda el archivo si existe
        $archivoPath = null;
        if ($request->hasFile('archivo_factura')) {
            $archivoPath = $request->file('archivo_factura')->store('facturas_gastos_varios', 'public');
        }

        // Crea el registro
        GastosVarios::create([
            'obra_id' => $request->obra_id,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
            'numero_factura' => $request->numero_factura,
            'importe' => $request->importe,
            'archivo_factura' => $archivoPath,
        ]);

        return redirect()->back()->with('success', 'Gasto Vario creado correctamente');
    }

    public function destroy($id)
    {
        $gastoVario = GastosVarios::findOrFail($id);

        if ($gastoVario->archivo_factura) {
            Storage::disk('public')->delete($gastoVario->archivo_factura);
        }

        $gastoVario->delete();

        return redirect()->back()->with('success', 'Gasto eliminado correctamente');
    }

    public function verInforme($id)
    {
        $obra = Obra::findOrFail($id);
        $gastos_varios = GastosVarios::where('obra_id', $id)->get();

        return view('obras.gastos.gastos-varios.pdf', compact('obra', 'gastos_varios'));
    }

    public function gastosVariosExcel(Request $request, $id)
    {
        $tipo = $request->input('tipo');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new GastosVariosObraExport($id, $tipo, $fechaInicio, $fechaFin),
            'gastos_varios_obra_'.$id.'.xlsx'
        );
    }

    public function descargarPDF(Request $request, $id)
    {
        // Buscar la obra
        $obra = Obra::findOrFail($id);

        // Aplicar filtros si existen
        $query = GastosVarios::where('obra_id', $id);

        // Filtro por nombre
        if ($request->filled('tipo')) {
            $query->where('tipo', 'LIKE', "%{$request->tipo}%");
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        // Obtener los registros filtrados
        $gastos_varios = $query->get();

        // Crear el PDF usando app()
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('obras.gastos.gastos-varios.pdf', compact('obra', 'gastos_varios'))
            ->setPaper('a4', 'portrait');

        // Descargar el PDF
        return $pdf->download('gastos_varios_obra_'.$id.'.pdf');
    }
}
