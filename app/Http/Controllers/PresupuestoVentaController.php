<?php

namespace App\Http\Controllers;

use App\Models\Obra;

class PresupuestoVentaController extends Controller
{
    public function index(int $obraId)
    {
        $obra = Obra::findOrFail($obraId);

        return view('obras.presupuesto-venta.index', [
            'obra' => $obra,
        ]);
    }

    public function global()
    {
        $obras = Obra::orderBy('nombre')
            ->get(['id', 'nombre', 'estado'])
            ->map(fn ($o) => [
                'id'     => $o->id,
                'nombre' => $o->nombre,
                'estado' => $o->estado ? ucfirst($o->estado) : null,
            ])
            ->values();

        return view('obras.presupuesto-venta.global', [
            'obras' => $obras,
        ]);
    }
}
