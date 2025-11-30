<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;

class LoginLogController extends Controller
{
    public function index()
    {
        // Obtener todos los registros, incluyendo la información del usuario relacionado
        $logs = LoginLog::with('user')->latest()->get();

        return view('users.logs', compact('logs'));
    }
}
