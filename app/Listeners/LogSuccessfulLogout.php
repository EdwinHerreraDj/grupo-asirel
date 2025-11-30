<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Logout;

/* Aquí registramos los Logout de los usuarios con se cierra la sesion */

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Logout $event)
    {
        // Registrar el cierre de sesión en la tabla login_logs
        LoginLog::where('user_id', $event->user->id)
            ->whereNull('logged_out_at') // Solo registra si el usuario no tiene un cierre previo
            ->latest()
            ->first()
            ->update(['logged_out_at' => now()]);
    }
}
