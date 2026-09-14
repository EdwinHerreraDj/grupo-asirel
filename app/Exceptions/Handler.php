<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // 419: token CSRF caducado (sesión expirada, pestaña abierta mucho tiempo,
        // página restaurada por el navegador...). En lugar de la pantalla
        // "419 PAGE EXPIRED", se devuelve al usuario a un sitio útil con aviso.
        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            // React (axios) y Livewire gestionan el 419 en el navegador.
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return response()->json([
                    'message' => 'Tu sesión ha caducado. Vuelve a iniciar sesión.',
                ], 419);
            }

            if ($request->user()) {
                return redirect()->back()
                    ->with('error', 'La página había caducado. Vuelve a intentarlo.');
            }

            return redirect()->route('login')
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->withErrors(['login' => 'Tu sesión había caducado. Vuelve a iniciar sesión.']);
        });
    }
}
