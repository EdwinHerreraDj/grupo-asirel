<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certificacion;

class CertificacionDetalleController extends Controller
{
    public function show(Certificacion $certificacion)
    {

        return view('obras.certificaciones.detalles', compact('certificacion'));
    }
}
   