<?php

namespace App\Http\Controllers;

use App\Models\Obra;

class GastosController extends Controller
{
    public function index($id)
    {
        $obra = Obra::findOrfail($id);

        return view('obras.gastos.index', compact('obra'));
    }
}
