<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Models\Cliente;
use App\Models\ObraPresupuestoVenta;
use App\Models\GastoInicialPartida;
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

        $empresa = \App\Models\Empresa::first();

        $capitulos = ObraPresupuestoVenta::where('obra_id', $obra->id)
            ->with([
                'oficio',
                'partidas' => fn($q) => $q->orderBy('orden')->orderBy('id'),
            ])
            ->get()
            ->map(fn($cap) => [
                'nombre'       => $cap->oficio->nombre ?? '—',
                'importe_total' => (float) $cap->importe_total,
                'partidas'     => $cap->partidas->map(fn($p) => [
                    'codigo'          => $p->codigo,
                    'descripcion'     => $p->descripcion,
                    'unidad'          => $p->unidad,
                    'medicion'        => (float) $p->medicion,
                    'precio_unitario' => (float) $p->precio_unitario,
                    'importe'         => (float) $p->importe,
                ])->toArray(),
            ])
            ->filter(fn($cap) => count($cap['partidas']) > 0)
            ->values();

        $total = $capitulos->sum('importe_total');

        $pdf = Pdf::loadView('pdf.presupuesto-venta-partidas', [
            'obra'      => $obra,
            'cliente'   => $cliente,
            'empresa'   => $empresa,
            'capitulos' => $capitulos,
            'total'     => $total,
            'fecha'     => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Presupuesto_' . $obra->id . '_' . now()->format('Ymd') . '.pdf'
        );
    }
}
