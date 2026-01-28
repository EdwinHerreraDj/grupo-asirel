<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use Illuminate\Http\Request;

class PresupuestoVentaController extends Controller
{
    public function index(int $obraId)
    {
        $obra = Obra::findOrFail($obraId);

        return view('obras.presupuesto-venta.index', [
            'obra' => $obra,
        ]);
    }
}
