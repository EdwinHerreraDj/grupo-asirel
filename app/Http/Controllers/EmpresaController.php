<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $empresa = Empresa::first();

        return view('empresa.index', compact('empresa'));
    }
}
