<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use Illuminate\Http\Request;

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
