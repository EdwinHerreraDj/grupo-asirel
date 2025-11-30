<?php

namespace App\Http\Controllers;

use App\Exports\SubcontratasObraExport;
use App\Models\Obra;
use App\Models\Subcontrata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SubcontrataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $obra = Obra::with('subcontratas')->findOrFail($id);

        return view('obras.gastos.subcontratas.index', compact('obra'));
    }

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
            'fecha' => 'required|date',
            'importe' => 'required|numeric',
            'archivo_factura' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
            'archivo_contrato' => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx,xls,xlsx,zip,rar',
        ]);

        // Guarda el archivo si existe
        $archivoFacturaPath = null;
        $archivoContratoPath = null;

        if ($request->hasFile('archivo_factura')) {
            $archivoFacturaPath = $request->file('archivo_factura')->store('subcontratas/facturas', 'public');
        }

        if ($request->hasFile('archivo_contrato')) {
            $archivoContratoPath = $request->file('archivo_contrato')->store('subcontratas/contratos', 'public');
        }

        // Crea el registro
        Subcontrata::create([
            'obra_id' => $request->obra_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
            'importe' => $request->importe,
            'archivo_factura' => $archivoFacturaPath,
            'archivo_contrato' => $archivoContratoPath,
        ]);

        return redirect()->back()->with('success', 'Subcontrata agregada correctamente');
    }

    public function destroy($id)
    {
        $subcontrata = Subcontrata::findOrFail($id);

        if ($subcontrata->archivo_factura) {
            Storage::disk('public')->delete($subcontrata->archivo_factura);
        }
        if ($subcontrata->archivo_contrato) {
            Storage::disk('public')->delete($subcontrata->archivo_contrato);
        }

        $subcontrata->delete();

        return redirect()->back()->with('success', 'Subcontrata eliminada correctamente.');
    }

    public function subcontratasExcel(Request $request, $id)
    {
        $nombre = $request->input('nombre');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new SubcontratasObraExport($id, $nombre, $fechaInicio, $fechaFin),
            'subcontratas_obra_'.$id.'.xlsx'
        );
    }

    public function verInforme($id)
    {
        $obra = Obra::findOrFail($id);
        $subcontratas = Subcontrata::where('obra_id', $id)->get();

        return view('obras.gastos.subcontratas.pdf', compact('obra', 'subcontratas'));
    }

    public function descargarPDF(Request $request, $id)
    {
        $obra = Obra::findOrFail($id);

        // Aplicar filtros si existen
        $query = Subcontrata::where('obra_id', $id);

        if ($request->filled('nombre')) {
            $query->where('nombre', $request->nombre);
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $subcontratas = $query->get();

        // Crear el PDF usando app() en lugar de Pdf::
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('obras.gastos.subcontratas.pdf', compact('obra', 'subcontratas'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('informe_obra_'.$id.'.pdf');
    }
}
