<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGastoEmpresa;
use Illuminate\Http\Request;

class CategoriaGastoEmpresaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaGastoEmpresa::with('parent')->get();

        return view('empresa.categorias_gastos_empresa.index', compact('categorias'));
    }
}
