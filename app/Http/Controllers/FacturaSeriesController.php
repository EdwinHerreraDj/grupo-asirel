<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacturaSeriesController extends Controller
{
    public function index()
    {
        return view('empresa.facturas.series.index');
    }
}
