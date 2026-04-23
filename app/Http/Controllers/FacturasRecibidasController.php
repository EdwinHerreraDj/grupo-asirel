<?php

namespace App\Http\Controllers;

use App\Models\Obra;

class FacturasRecibidasController extends Controller
{
    public function index(Obra $obra)
    {
        return view('obras.facturas.recibidas.index', compact('obra'));
    }

    public function global()
    {
        return view('obras.facturas.recibidas.global');
    }
}
