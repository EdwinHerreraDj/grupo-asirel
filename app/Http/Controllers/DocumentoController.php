<?php

namespace App\Http\Controllers;

use App\Models\Obra;

class DocumentoController extends Controller
{
    public function index($obraId)
    {
        $obra = Obra::with('documentos')->findOrFail($obraId);

        return view('obras.docs', compact('obra'));
    }
}
