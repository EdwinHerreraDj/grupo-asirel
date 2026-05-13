<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\GastoInicialPartida;
use App\Models\Obra;
use App\Models\ObraPresupuestoVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PresupuestoVentaPdfController extends Controller
{
    public function descargar(Request $request, Obra $obra)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
        ]);

        $cliente = Cliente::findOrFail($request->cliente_id);
        $empresa = Empresa::first();

        // Para obras tipo CONTRATISTA, la proforma se genera a partir del COSTE TEÓRICO.
        // Para SUBCONTRATISTA (default), se usa el PRESUPUESTO DE VENTA.
        $esContratista = ($obra->tipo ?? 'subcontratista') === 'contratista';

        $capitulos = $esContratista
            ? $this->capitulosDesdeCosteTeorico($obra)
            : $this->capitulosDesdePresupuestoVenta($obra);

        $total = $capitulos->sum('importe_total');

        $pdf = Pdf::loadView('pdf.presupuesto-venta-partidas', [
            'obra'      => $obra,
            'cliente'   => $cliente,
            'empresa'   => $empresa,
            'capitulos' => $capitulos,
            'total'     => $total,
            'fecha'     => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        $sufijo = $esContratista ? 'contratista' : 'subcontratista';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'Presupuesto_' . $sufijo . '_' . $obra->id . '_' . now()->format('Ymd') . '.pdf'
        );
    }

    /**
     * Fuente normal: presupuesto de venta (precios con margen aplicado al cliente).
     */
    private function capitulosDesdePresupuestoVenta(Obra $obra)
    {
        return ObraPresupuestoVenta::where('obra_id', $obra->id)
            ->with([
                'oficio',
                'partidas' => fn ($q) => $q->orderBy('orden')->orderBy('id'),
            ])
            ->get()
            ->map(fn ($cap) => [
                'nombre'       => $cap->oficio->nombre ?? '—',
                'importe_total' => (float) $cap->importe_total,
                'partidas'     => $cap->partidas->map(fn ($p) => [
                    'codigo'          => $p->codigo,
                    'descripcion'     => $p->descripcion,
                    'unidad'          => $p->unidad,
                    'medicion'        => (float) $p->medicion,
                    'precio_unitario' => (float) $p->precio_unitario,
                    'importe'         => (float) $p->importe,
                ])->toArray(),
            ])
            ->filter(fn ($cap) => count($cap['partidas']) > 0)
            ->values();
    }

    /**
     * Fuente para contratista: coste teórico (precios al coste, sin margen comercial).
     * Las partidas se agrupan por su categoría (oficio).
     */
    private function capitulosDesdeCosteTeorico(Obra $obra)
    {
        $partidas = GastoInicialPartida::where('obra_id', $obra->id)
            ->where('activo', true)
            ->with('categoria')
            ->orderBy('obra_gasto_categoria_id')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return $partidas
            ->groupBy('obra_gasto_categoria_id')
            ->map(function ($grupo) {
                $partidas = $grupo->map(fn ($p) => [
                    'codigo'          => $p->codigo,
                    'descripcion'     => $p->descripcion,
                    'unidad'          => $p->unidad,
                    'medicion'        => (float) $p->medicion,
                    'precio_unitario' => (float) $p->precio_unitario,
                    'importe'         => (float) $p->importe,
                ])->values()->toArray();

                return [
                    'nombre'       => $grupo->first()->categoria->nombre ?? 'Capítulo',
                    'importe_total' => $grupo->sum('importe'),
                    'partidas'     => $partidas,
                ];
            })
            ->values();
    }
}
