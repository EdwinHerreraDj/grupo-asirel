<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

/* Aquí registramos los logs de los usuarios generando un registro en el base de datos en login_logs */

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {
        // Registrar el inicio de sesión
        LoginLog::create([
            'user_id' => $event->user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'logged_in_at' => now(),
        ]);
    }
}
