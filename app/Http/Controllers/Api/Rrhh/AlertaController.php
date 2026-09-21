<?php

namespace App\Http\Controllers\Api\Rrhh;

use App\Http\Controllers\Controller;
use App\Services\Rrhh\AlertasRrhh;

/** Panel de alertas de Recursos humanos. */
class AlertaController extends Controller
{
    public function index(AlertasRrhh $alertas)
    {
        return response()->json($alertas->calcular());
    }
}
