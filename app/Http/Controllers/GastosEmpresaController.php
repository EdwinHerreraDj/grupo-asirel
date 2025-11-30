<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GastosEmpresaController extends Controller
{
    public function index()
    {
        return view('empresa.gastos_empresa.index');
    }
}
