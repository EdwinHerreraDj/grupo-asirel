<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Obra;

class CertificacionController extends Controller
{
    public function index(Request $request, $id)
    {
        $obra = Obra::findOrFail($id);

        return view('obras.certificaciones.certificacion', [
            'obra' => $obra,
        ]);
    }
}
